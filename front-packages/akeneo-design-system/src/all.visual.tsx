import 'expect-puppeteer';
import fs from 'fs';
import {toMatchImageSnapshot} from 'jest-image-snapshot';

expect.extend({toMatchImageSnapshot});

const EXCLUDE = ['Components/Modal', 'Components/Inputs/Select input'];

// Storybook 8 emits the story index (index.json, v5 format) as part of the
// static build — it replaces the `sb extract` stories.json of storybook 6.
// `title` is the v5 name for the old `kind`, with identical values, so the
// jest-image-snapshot filenames derived from the test names stay unchanged.
type StoryIndex = {
  entries: {
    [storyKey: string]: {
      id: string;
      title: string;
      name: string;
      type: 'story' | 'docs';
    };
  };
};

const indexFileContent = fs.readFileSync('./storybook-static/index.json').toString('utf8');
const storyIndex = JSON.parse(indexFileContent) as StoryIndex;
const stories = Object.values(storyIndex.entries)
  .filter(story => 'story' === story.type && 0 === story.id.indexOf('components') && !EXCLUDE.includes(story.title))
  .map(story => [story.title, story.name, story.id]);

describe('Visual tests', () => {
  test.each(stories)('Renders %s/%s correctly', async (_kind, _name, id) => {
    await page.goto(`http://localhost:6006/iframe.html?id=${id}`);
    const root = await page.$('#storybook-root');
    if (null === root) {
      // Fail loudly: a missing mount node means the story did not render at
      // all (or the selector drifted again, as in the storybook 6 → 8 #root
      // → #storybook-root rename) — silently returning would green-light a
      // test that screenshotted nothing.
      throw new Error(`Story ${id}: #storybook-root not found — the story did not render`);
    }

    const image = await root.screenshot();

    expect(image).toMatchImageSnapshot({
      failureThreshold: 0.5,
      failureThresholdType: 'percent',
    });
  });
});
