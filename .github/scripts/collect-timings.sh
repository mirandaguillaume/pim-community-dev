#!/bin/bash
#
# Collect test durations from JUnit XML reports and produce/update a JSON timing file.
#
# Usage:
#   collect-timings.sh JUNIT_GLOB OUTPUT_JSON [EXISTING_JSON]
#
# Arguments:
#   JUNIT_GLOB    - Glob pattern for JUnit XML files (e.g. "var/tests/phpunit/**/*.xml")
#   OUTPUT_JSON   - Path where the merged timing JSON will be written
#   EXISTING_JSON - Optional existing timing JSON to merge with (preserves old data)
#
# Output: JSON object mapping test file paths to total duration in seconds.
#         e.g. {"tests/back/Foo/BarIntegration.php": 12.34, ...}

set -eo pipefail

JUNIT_GLOB="$1"
OUTPUT_JSON="$2"
EXISTING_JSON="${3:-}"

python3 -c "
import glob, json, sys, os
from xml.etree import ElementTree

junit_glob = '$JUNIT_GLOB'
output_json = '$OUTPUT_JSON'
existing_json = '$EXISTING_JSON'

# Load existing timings if provided
timings = {}
if existing_json and os.path.isfile(existing_json):
    with open(existing_json) as f:
        timings = json.load(f)

# Parse JUnit XML files
xml_files = glob.glob(junit_glob, recursive=True)
print(f'Parsing {len(xml_files)} JUnit XML files', file=sys.stderr)

new_timings = {}
for xml_file in xml_files:
    try:
        tree = ElementTree.parse(xml_file)
    except ElementTree.ParseError:
        print(f'Warning: failed to parse {xml_file}', file=sys.stderr)
        continue

    for testcase in tree.iter('testcase'):
        time_str = testcase.get('time', '0')
        filepath = testcase.get('file', '')
        try:
            duration = float(time_str)
        except ValueError:
            continue
        if not filepath:
            # Try to reconstruct from classname
            classname = testcase.get('classname', '')
            if classname:
                filepath = classname.replace('.', '/') + '.php'
            else:
                continue

        # Normalize: strip leading /srv/pim/ if present (Docker mount path)
        if filepath.startswith('/srv/pim/'):
            filepath = filepath[len('/srv/pim/'):]

        new_timings[filepath] = new_timings.get(filepath, 0) + duration

# Merge: new data overwrites old for the same keys
timings.update(new_timings)

os.makedirs(os.path.dirname(output_json) or '.', exist_ok=True)
with open(output_json, 'w') as f:
    json.dump(timings, f, indent=2, sort_keys=True)

print(f'Wrote {len(timings)} entries ({len(new_timings)} new/updated) to {output_json}', file=sys.stderr)
"
