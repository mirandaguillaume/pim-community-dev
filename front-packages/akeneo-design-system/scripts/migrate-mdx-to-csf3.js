#!/usr/bin/env node

/**
 * Storybook 6→8 MDX Migration Script
 *
 * This script converts .stories.mdx files (Storybook 6 format) into:
 * 1. A .stories.tsx CSF3 file with story definitions
 * 2. A .mdx documentation file that references the CSF3 stories
 *
 * Usage: node scripts/migrate-mdx-to-csf3.js [--dry-run]
 *
 * The conversion handles:
 * - Extracting <Meta> props into CSF3 default export
 * - Converting <Story name="..."> blocks into named exports
 * - Converting <ArgsTable> to <ArgTypes>
 * - Updating import sources from @storybook/addon-docs to @storybook/blocks
 * - Handling render functions with args and useState
 */

const fs = require('fs');
const path = require('path');

/**
 * Simple recursive glob implementation using only Node.js built-ins.
 */
function globSync(pattern, dir) {
  const results = [];
  const regex = new RegExp(pattern.replace(/\*/g, '.*').replace(/\?/g, '.'));

  function walk(currentDir) {
    const entries = fs.readdirSync(currentDir, {withFileTypes: true});
    for (const entry of entries) {
      const fullPath = path.join(currentDir, entry.name);
      if (entry.isDirectory()) {
        walk(fullPath);
      } else if (entry.isFile() && entry.name.endsWith('.stories.mdx')) {
        results.push(fullPath);
      }
    }
  }

  walk(dir);
  return results;
}

const DRY_RUN = process.argv.includes('--dry-run');
const SRC_DIR = path.resolve(__dirname, '../src');
const GENERATOR_DIR = path.resolve(__dirname, '../generator');

/**
 * Convert a story name like "Standard" into a valid JS identifier like "Standard"
 * and handle names like "With an Icon" -> "WithAnIcon"
 */
function storyNameToExportName(name) {
  return name
    .replace(/[^a-zA-Z0-9\s]/g, '')
    .split(/\s+/)
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join('');
}

/**
 * Parse a .stories.mdx file and extract:
 * - imports (non-storybook)
 * - Meta props
 * - Story definitions (name, render function body, whether it uses args)
 * - MDX documentation content
 */
function parseMdxFile(content, filePath) {
  const lines = content.split('\n');

  // Extract all imports
  const imports = [];
  const storybookImports = [];
  let i = 0;

  // Find import block at top of file
  while (i < lines.length) {
    const line = lines[i].trim();
    if (line.startsWith('import ')) {
      if (line.includes('@storybook/')) {
        storybookImports.push(line);
      } else {
        imports.push(line);
      }
      i++;
      continue;
    }
    if (line === '' && i < 5) {
      i++;
      continue;
    }
    break;
  }

  // Extract Meta block
  let metaBlock = '';
  let metaTitle = '';
  let metaComponent = '';
  let metaArgTypes = '';
  let metaArgs = '';
  let inMeta = false;
  let metaDepth = 0;
  let metaStartLine = -1;
  let metaEndLine = -1;

  for (let j = 0; j < lines.length; j++) {
    const line = lines[j];
    if (line.trim().startsWith('<Meta')) {
      inMeta = true;
      metaStartLine = j;
      metaBlock = '';
    }
    if (inMeta) {
      metaBlock += line + '\n';
      if (line.includes('/>') || (line.trim() === '>' && !line.includes('<'))) {
        metaEndLine = j;
        inMeta = false;
      }
    }
  }

  // Parse meta block for title and component
  const titleMatch = metaBlock.match(/title="([^"]+)"/);
  if (titleMatch) metaTitle = titleMatch[1];

  const componentMatch = metaBlock.match(/component=\{(\w+)\}/);
  if (componentMatch) metaComponent = componentMatch[1];

  // Extract argTypes block from Meta (rough extraction)
  const argTypesMatch = metaBlock.match(/argTypes=\{(\{[\s\S]*?\})\}\s*(?:args=|\/?>)/);
  if (argTypesMatch) metaArgTypes = argTypesMatch[1];

  // Extract args block from Meta
  const argsMatch = metaBlock.match(/args=\{(\{[\s\S]*?\})\}\s*\/?>/);
  if (argsMatch) metaArgs = argsMatch[1];

  // Extract Story blocks
  const stories = [];
  let inStory = false;
  let storyContent = '';
  let storyName = '';
  let storyDepth = 0;
  let inCanvas = false;

  for (let j = 0; j < lines.length; j++) {
    const line = lines[j];

    // Detect <Story name="...">
    const storyMatch = line.match(/<Story\s+name="([^"]+)"[^>]*>/);
    if (storyMatch) {
      inStory = true;
      storyName = storyMatch[1];
      storyContent = '';
      storyDepth = 1;
      continue;
    }

    if (inStory) {
      if (line.includes('<Story')) storyDepth++;
      if (line.includes('</Story>')) {
        storyDepth--;
        if (storyDepth === 0) {
          inStory = false;
          stories.push({
            name: storyName,
            content: storyContent.trim(),
          });
          continue;
        }
      }
      storyContent += line + '\n';
    }
  }

  // Determine which stories use args
  stories.forEach(story => {
    story.usesArgs = story.content.includes('{args =>') || story.content.includes('{(args)');
    story.usesState = story.content.includes('useState');

    // Clean render body: remove the {args => { return (...) }} wrapper or {() => { return (...) }} wrapper
    let body = story.content.trim();

    // Pattern: {args => { return (...); }}
    const argsReturnMatch = body.match(/^\{args\s*=>\s*\{[\s\S]*?return\s*\(([\s\S]*)\);\s*\}\}$/);
    if (argsReturnMatch) {
      story.renderBody = argsReturnMatch[1].trim();
      story.renderType = 'args-return';
      return;
    }

    // Pattern: {args => { ... return <...> }}  (no parens)
    const argsReturnNoParen = body.match(/^\{args\s*=>\s*\{([\s\S]*)\}\}$/);
    if (argsReturnNoParen && story.usesState) {
      story.renderBody = argsReturnNoParen[1].trim();
      story.renderType = 'args-stateful';
      return;
    }

    // Pattern: {args => { return <...>; }}
    const argsReturnSimple = body.match(/^\{args\s*=>\s*\{\s*return\s+([\s\S]*?);\s*\}\}$/);
    if (argsReturnSimple) {
      story.renderBody = argsReturnSimple[1].trim();
      story.renderType = 'args-return-simple';
      return;
    }

    // Pattern: {args => (...)}
    const argsParenMatch = body.match(/^\{args\s*=>\s*\{?\s*\(?([\s\S]*?)\)?\s*\}?\}$/);
    if (argsParenMatch && story.usesArgs) {
      story.renderBody = argsParenMatch[1].trim();
      story.renderType = 'args-inline';
      return;
    }

    // Pattern: {() => { return (...); }}
    const noArgsReturnMatch = body.match(/^\{\(\)\s*=>\s*\{[\s\S]*?return\s*\(([\s\S]*)\);\s*\}\}$/);
    if (noArgsReturnMatch) {
      story.renderBody = noArgsReturnMatch[1].trim();
      story.renderType = 'no-args-return';
      return;
    }

    // Pattern: {() => (...)}
    const noArgsMatch = body.match(/^\{\(\)\s*=>\s*\{?\s*\(?([\s\S]*?)\)?\s*\}?\}$/);
    if (noArgsMatch) {
      story.renderBody = noArgsMatch[1].trim();
      story.renderType = 'no-args-inline';
      return;
    }

    // Direct JSX (no wrapper function)
    story.renderBody = body;
    story.renderType = 'direct';
  });

  // Check for exported values in MDX (like Colors.stories.mdx has export const)
  const exports = [];
  for (const line of lines) {
    if (line.startsWith('export ')) {
      exports.push(line);
    }
  }

  // Check for <LinkTo> usage
  const usesLinkTo = content.includes('<LinkTo');

  return {
    imports,
    storybookImports,
    metaTitle,
    metaComponent,
    metaArgTypes,
    metaArgs,
    metaBlock,
    metaStartLine,
    metaEndLine,
    stories,
    exports,
    usesLinkTo,
    fullContent: content,
  };
}

/**
 * Determine which storybook block imports are needed for the new MDX doc file
 */
function getDocMdxImports(parsed) {
  const needed = new Set(['Meta']);
  if (parsed.stories.length > 0) {
    needed.add('Canvas');
    needed.add('Story');
  }
  if (parsed.fullContent.includes('<ArgsTable') || parsed.fullContent.includes('ArgsTable')) {
    needed.add('ArgTypes');
  }
  if (parsed.fullContent.includes('<Controls')) {
    needed.add('Controls');
  }
  return Array.from(needed);
}

/**
 * Generate the new documentation-only MDX file
 */
function generateDocMdx(parsed, csfFileName) {
  const blockImports = getDocMdxImports(parsed);
  let output = '';

  // Import blocks from @storybook/blocks
  output += `import {${blockImports.join(', ')}} from '@storybook/blocks';\n`;

  // Import the CSF stories
  if (parsed.stories.length > 0) {
    const storyExports = parsed.stories.map(s => storyNameToExportName(s.name));
    output += `import * as Stories from './${csfFileName}';\n`;
  }

  // Re-add non-storybook imports that the MDX doc content needs
  // (styled-components, components for display, etc.)
  for (const imp of parsed.imports) {
    // Skip imports of the main component (those go in the CSF file)
    output += imp + '\n';
  }

  output += '\n';
  output += `<Meta of={Stories} />\n`;

  // Now output the rest of the MDX content, replacing:
  // - <Canvas><Story name="X">...</Story></Canvas> with <Canvas of={Stories.X} />
  // - <ArgsTable story="X" /> with <ArgTypes of={Stories.X} />
  // - <ArgsTable /> with <ArgTypes />
  const lines = parsed.fullContent.split('\n');
  let skip = false;
  let skipUntil = '';
  let inImportBlock = true;
  let pastMeta = false;
  let inMetaBlock = false;

  for (let j = 0; j < lines.length; j++) {
    const line = lines[j];
    const trimmed = line.trim();

    // Skip original import lines (we already handled imports above)
    if (inImportBlock) {
      if (trimmed.startsWith('import ') || trimmed === '') {
        continue;
      }
      inImportBlock = false;
    }

    // Skip original Meta block
    if (trimmed.startsWith('<Meta')) {
      inMetaBlock = true;
      continue;
    }
    if (inMetaBlock) {
      if (trimmed.includes('/>') || trimmed === '>') {
        inMetaBlock = false;
        pastMeta = true;
      }
      continue;
    }

    // Skip export lines (these go in the CSF file or are not needed)
    if (trimmed.startsWith('export ')) {
      continue;
    }

    // Replace <Canvas><Story name="X">...</Story></Canvas> blocks
    const canvasStoryMatch = trimmed.match(/^<Canvas>/);
    if (canvasStoryMatch) {
      // Look ahead for the Story name
      let storyNameInCanvas = null;
      for (let k = j + 1; k < lines.length && k < j + 3; k++) {
        const storyMatch = lines[k].match(/<Story\s+name="([^"]+)"[^>]*>/);
        if (storyMatch) {
          storyNameInCanvas = storyMatch[1];
          break;
        }
      }
      if (storyNameInCanvas) {
        const exportName = storyNameToExportName(storyNameInCanvas);
        output += `<Canvas of={Stories.${exportName}} />\n`;
        // Skip until </Canvas>
        skip = true;
        skipUntil = '</Canvas>';
        continue;
      }
    }

    if (skip) {
      if (trimmed.includes(skipUntil)) {
        skip = false;
      }
      continue;
    }

    // Replace <ArgsTable story="X" />
    const argsTableMatch = trimmed.match(/<ArgsTable\s+story="([^"]+)"\s*\/>/);
    if (argsTableMatch) {
      const exportName = storyNameToExportName(argsTableMatch[1]);
      output += `<ArgTypes of={Stories.${exportName}} />\n`;
      continue;
    }

    // Replace standalone <ArgsTable />
    if (trimmed === '<ArgsTable />') {
      output += '<ArgTypes />\n';
      continue;
    }

    output += line + '\n';
  }

  return output.trimEnd() + '\n';
}

/**
 * Generate CSF3 stories file
 */
function generateCsf3(parsed, mdxRelDir) {
  let output = '';

  // Imports
  output += `import type {Meta, StoryObj} from '@storybook/react';\n`;

  // Add React import (needed for JSX) and useState if any story uses it
  const needsUseState = parsed.stories.some(s => s.usesState);
  if (needsUseState) {
    output += `import React, {useState} from 'react';\n`;
  } else {
    output += `import React from 'react';\n`;
  }

  // Add component imports from the original MDX, skipping duplicates
  // (e.g. if the original MDX already had `import {useState} from 'react'`)
  for (const imp of parsed.imports) {
    // Skip React/useState imports since we already added them above
    if (imp.match(/from\s+['"]react['"]/)) continue;
    // Skip @storybook imports that were already classified as storybook imports
    if (imp.match(/from\s+['"]@storybook\//)) continue;
    output += imp + '\n';
  }

  // Add exports from original MDX (like styled components)
  for (const exp of parsed.exports) {
    output += exp + '\n';
  }

  output += '\n';

  // Default export (meta)
  output += `const meta: Meta<typeof ${parsed.metaComponent || 'any'}> = {\n`;
  output += `  title: '${parsed.metaTitle}',\n`;
  if (parsed.metaComponent) {
    output += `  component: ${parsed.metaComponent},\n`;
  }
  if (parsed.metaArgTypes) {
    output += `  argTypes: ${parsed.metaArgTypes},\n`;
  }
  if (parsed.metaArgs) {
    output += `  args: ${parsed.metaArgs},\n`;
  }
  output += `};\n\n`;
  output += `export default meta;\n`;
  output += `type Story = StoryObj<typeof meta>;\n\n`;

  // Named story exports
  for (const story of parsed.stories) {
    const exportName = storyNameToExportName(story.name);

    if (story.usesState || story.renderType === 'args-stateful') {
      // Stories with useState need a render function
      output += `export const ${exportName}: Story = {\n`;
      output += `  name: '${story.name}',\n`;
      output += `  render: (args) => {\n`;
      output += `    ${story.renderBody}\n`;
      output += `  },\n`;
      output += `};\n\n`;
    } else if (story.usesArgs) {
      output += `export const ${exportName}: Story = {\n`;
      output += `  name: '${story.name}',\n`;
      output += `  render: (args) => (\n`;
      output += `    ${story.renderBody}\n`;
      output += `  ),\n`;
      output += `};\n\n`;
    } else if (story.renderType === 'direct') {
      output += `export const ${exportName}: Story = {\n`;
      output += `  name: '${story.name}',\n`;
      output += `  render: () => (\n`;
      output += `    ${story.renderBody}\n`;
      output += `  ),\n`;
      output += `};\n\n`;
    } else {
      output += `export const ${exportName}: Story = {\n`;
      output += `  name: '${story.name}',\n`;
      output += `  render: () => (\n`;
      output += `    ${story.renderBody}\n`;
      output += `  ),\n`;
      output += `};\n\n`;
    }
  }

  return output;
}

// Main
function main() {
  const mdxFiles = globSync('**/*.stories.mdx', SRC_DIR);
  const generatorFiles = fs.existsSync(GENERATOR_DIR) ? globSync('**/*.stories.mdx', GENERATOR_DIR) : [];
  const allFiles = [...mdxFiles, ...generatorFiles];

  console.log(`Found ${allFiles.length} .stories.mdx files to migrate`);

  const results = {success: 0, skipped: 0, errors: []};

  for (const file of allFiles) {
    const content = fs.readFileSync(file, 'utf-8');
    const parsed = parseMdxFile(content, file);
    const dir = path.dirname(file);
    const baseName = path.basename(file, '.stories.mdx');

    console.log(`\nProcessing: ${path.relative(path.resolve(__dirname, '..'), file)}`);
    console.log(`  Title: ${parsed.metaTitle}`);
    console.log(`  Component: ${parsed.metaComponent || 'none'}`);
    console.log(`  Stories: ${parsed.stories.map(s => s.name).join(', ') || 'none'}`);

    if (parsed.stories.length === 0) {
      // Docs-only MDX: just update imports
      const newContent = content
        .replace(/@storybook\/addon-docs/g, '@storybook/blocks')
        .replace(/ArgsTable/g, 'ArgTypes');

      if (DRY_RUN) {
        console.log(`  [DRY RUN] Would update imports in ${file}`);
      } else {
        fs.writeFileSync(file, newContent);
        console.log(`  Updated imports (docs-only file)`);
      }
      results.success++;
      continue;
    }

    // Has stories: generate CSF3 companion file and update MDX
    const csfFileName = `${baseName}.stories`;
    const csfFilePath = path.join(dir, `${csfFileName}.tsx`);
    const docMdxPath = file; // Keep same path but rewrite content

    try {
      const csfContent = generateCsf3(parsed, dir);
      const docMdxContent = generateDocMdx(parsed, csfFileName);

      if (DRY_RUN) {
        console.log(`  [DRY RUN] Would create: ${path.relative(path.resolve(__dirname, '..'), csfFilePath)}`);
        console.log(`  [DRY RUN] Would update: ${path.relative(path.resolve(__dirname, '..'), docMdxPath)}`);
      } else {
        fs.writeFileSync(csfFilePath, csfContent);
        // Rename .stories.mdx to .mdx (remove .stories from name so Storybook treats it as docs-only)
        const newDocPath = path.join(dir, `${baseName}.mdx`);
        fs.writeFileSync(newDocPath, docMdxContent);
        fs.unlinkSync(file); // Remove old .stories.mdx
        console.log(`  Created CSF3: ${path.relative(path.resolve(__dirname, '..'), csfFilePath)}`);
        console.log(`  Created doc:  ${path.relative(path.resolve(__dirname, '..'), newDocPath)}`);
        console.log(`  Removed old:  ${path.relative(path.resolve(__dirname, '..'), file)}`);
      }
      results.success++;
    } catch (err) {
      console.error(`  ERROR: ${err.message}`);
      results.errors.push({file, error: err.message});
    }
  }

  console.log(`\n=== Migration Summary ===`);
  console.log(`Success: ${results.success}`);
  console.log(`Errors: ${results.errors.length}`);
  if (results.errors.length > 0) {
    for (const e of results.errors) {
      console.log(`  - ${e.file}: ${e.error}`);
    }
  }
}

main();
