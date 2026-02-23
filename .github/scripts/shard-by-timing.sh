#!/bin/bash
#
# Timing-aware test sharding using greedy bin-packing.
# Falls back to hash-based sharding when no timing data is available.
#
# Usage:
#   echo "$TEST_FILES" | shard-by-timing.sh TIMING_FILE SHARD_NUM TOTAL_SHARDS DEFAULT_DURATION
#
# Arguments:
#   TIMING_FILE      - JSON file mapping test paths to durations (seconds).
#                      If missing or empty, falls back to hash-based sharding.
#   SHARD_NUM        - Current shard number (1-based)
#   TOTAL_SHARDS     - Total number of shards
#   DEFAULT_DURATION - Default duration (seconds) for tests not in TIMING_FILE
#
# Input:  test file paths on stdin (one per line)
# Output: test file paths assigned to this shard (one per line)

set -eo pipefail

TIMING_FILE="$1"
SHARD_NUM="$2"
TOTAL_SHARDS="$3"
DEFAULT_DURATION="${4:-30}"

# Read all test paths from stdin
TEST_PATHS=()
while IFS= read -r line; do
  [[ -n "$line" ]] && TEST_PATHS+=("$line")
done

if [[ ${#TEST_PATHS[@]} -eq 0 ]]; then
  exit 0
fi

# If timing file exists and is non-empty, use bin-packing
if [[ -s "$TIMING_FILE" ]]; then
  printf '%s\n' "${TEST_PATHS[@]}" | python3 -c "
import json, sys

timing_file = '$TIMING_FILE'
shard_num = int('$SHARD_NUM')
total_shards = int('$TOTAL_SHARDS')
default_dur = float('$DEFAULT_DURATION')

with open(timing_file) as f:
    timings = json.load(f)

tests = [line.strip() for line in sys.stdin if line.strip()]

# Sort by duration descending (greedy bin-packing: largest first)
tests_with_dur = [(t, timings.get(t, default_dur)) for t in tests]
tests_with_dur.sort(key=lambda x: -x[1])

# Greedy: assign each test to the lightest bin
bins = [0.0] * total_shards
assignments = [[] for _ in range(total_shards)]

for test, dur in tests_with_dur:
    lightest = min(range(total_shards), key=lambda i: bins[i])
    bins[lightest] += dur
    assignments[lightest].append(test)

# Log shard weights to stderr
for i in range(total_shards):
    count = len(assignments[i])
    weight = bins[i]
    marker = ' <-- this shard' if i == shard_num - 1 else ''
    print(f'Shard {i+1}/{total_shards}: {count} tests, est {weight:.0f}s{marker}', file=sys.stderr)

for test in assignments[shard_num - 1]:
    print(test)
"
else
  # Fallback: hash-based sharding (same as original cksum approach)
  echo "No timing data ($TIMING_FILE), using hash-based sharding" >&2
  for path in "${TEST_PATHS[@]}"; do
    HASH=$(echo -n "$path" | cksum | cut -d' ' -f1)
    ASSIGNED=$(( (HASH % TOTAL_SHARDS) + 1 ))
    if [[ "$ASSIGNED" -eq "$SHARD_NUM" ]]; then
      echo "$path"
    fi
  done
fi
