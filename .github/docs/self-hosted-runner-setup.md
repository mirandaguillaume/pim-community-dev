# Self-Hosted GitHub Actions Runner Setup

Guide for setting up a Hetzner CCX33 (8 vCPU / 32 GB / NVMe) as a self-hosted CI runner.

Estimated CI time: **10-15 min** (vs ~50 min on GitHub-hosted).
Cost: **45 EUR/month** fixed.

## Prerequisites

- A GitHub account with admin access to the repository
- An SSH key pair for server access

## Step 1: Create the Hetzner server

1. Go to [cloud.hetzner.com](https://cloud.hetzner.com) > New Project > Add Server
2. Configuration:
   - **Location**: Falkenstein (EU-Central)
   - **Image**: Ubuntu 24.04
   - **Type**: CCX33 (8 dedicated vCPU / 32 GB RAM / 160 GB NVMe)
   - **SSH Key**: add your public key
   - **Name**: `ci-runner-01`
3. Click "Create & Buy" — you get an IP address in seconds

## Step 2: Install dependencies

```bash
ssh root@<SERVER_IP>
```

Run the install script:

```bash
bash <(curl -sSL https://raw.githubusercontent.com/mirandaguillaume/pim-community-dev/master/.github/scripts/setup-runner.sh)
```

Or copy-paste manually:

```bash
#!/bin/bash
set -euo pipefail

echo "=== System update ==="
apt-get update && apt-get upgrade -y
apt-get install -y \
  curl git jq unzip acl \
  docker.io docker-compose-plugin \
  php8.3-cli php8.3-xml php8.3-mbstring

systemctl enable docker && systemctl start docker

echo "=== Create runner user ==="
id runner &>/dev/null || useradd -m -s /bin/bash -G docker runner
loginctl enable-linger runner

echo "=== Pre-pull Docker images (persistent cache) ==="
docker pull mysql:8.0.30 &
docker pull elastic/elasticsearch:8.11.3 &
docker pull selenium/standalone-chrome-debug:3.141.59 &
docker pull minio/minio:RELEASE.2025-01-20T14-49-07Z &
docker pull google/cloud-sdk:506.0.0-emulators &
docker pull fsouza/fake-gcs-server:1.45 &
docker pull akeneo/node:18 &
wait

echo "=== Install GitHub Actions Runner ==="
cd /home/runner
mkdir -p actions-runner && cd actions-runner

RUNNER_VERSION=$(curl -s https://api.github.com/repos/actions/runner/releases/latest \
  | jq -r .tag_name | sed 's/v//')
curl -o actions-runner.tar.gz -L \
  "https://github.com/actions/runner/releases/download/v${RUNNER_VERSION}/actions-runner-linux-x64-${RUNNER_VERSION}.tar.gz"
tar xzf actions-runner.tar.gz && rm actions-runner.tar.gz
chown -R runner:runner /home/runner/actions-runner

echo "=== Setup weekly cleanup cron ==="
cat > /etc/cron.weekly/cleanup-runner << 'CRON'
#!/bin/bash
find /home/runner/actions-runner/_work -maxdepth 2 -mtime +7 -type d -exec rm -rf {} + 2>/dev/null
docker system prune -f --filter "until=168h"
CRON
chmod +x /etc/cron.weekly/cleanup-runner

echo ""
echo "============================================"
echo " Server ready. Proceed to Step 3."
echo "============================================"
```

## Step 3: Register the runner on GitHub

1. Go to your repo on GitHub > **Settings** > **Actions** > **Runners** > **New self-hosted runner**
2. Copy the registration **token** displayed by GitHub
3. On the server:

```bash
su - runner
cd ~/actions-runner

# Register (replace TOKEN and REPO_URL)
./config.sh \
  --url https://github.com/mirandaguillaume/pim-community-dev \
  --token YOUR_TOKEN_HERE \
  --name ci-runner-01 \
  --labels self-hosted,linux,x64 \
  --work _work \
  --replace

# Install and start as a systemd service
sudo ./svc.sh install runner
sudo ./svc.sh start
```

4. Verify the runner shows as **green/idle** in Settings > Runners

## Step 4: Activate in CI

1. Go to **Settings** > **Secrets and variables** > **Actions** > **Variables**
2. Create a repository variable: `RUNNER_LABEL` = `self-hosted`
3. Done. The next CI run uses your server.

To revert to GitHub-hosted runners: delete the `RUNNER_LABEL` variable.

## Monitoring

```bash
# Live runner logs
sudo journalctl -u actions.runner.mirandaguillaume-pim-community-dev.ci-runner-01 -f

# Disk usage
df -h /home/runner

# Docker disk usage
docker system df
```

## Troubleshooting

### Runner shows offline

```bash
sudo ./svc.sh status
sudo ./svc.sh stop && sudo ./svc.sh start
```

### Disk full

```bash
# Nuclear cleanup
docker system prune -af
rm -rf /home/runner/actions-runner/_work/*/
```

### Re-register after token expires

1. Get a new token from Settings > Runners > New self-hosted runner
2. Run:

```bash
su - runner
cd ~/actions-runner
./config.sh remove --token OLD_TOKEN
./config.sh \
  --url https://github.com/mirandaguillaume/pim-community-dev \
  --token NEW_TOKEN \
  --name ci-runner-01 \
  --labels self-hosted,linux,x64 \
  --work _work \
  --replace
sudo ./svc.sh start
```

## Architecture

```
+---------------------------------------------+
|  Hetzner CCX33 (8 vCPU / 32 GB / NVMe)     |
|                                              |
|  GitHub Actions Runner (systemd service)     |
|                                              |
|  Persistent Docker cache:                    |
|  - mysql:8.0.30                              |
|  - elasticsearch:8.11.3                      |
|  - selenium/standalone-chrome-debug:3.141.59 |
|  - minio, pubsub-emulator, gcs-emulator     |
|  - akeneo/node:18                            |
|  - akeneo/pim-php-dev:8.2 (built by CI)     |
|                                              |
|  Persistent caches (NVMe):                   |
|  - composer vendor/                          |
|  - node_modules/                             |
|  - .phpstan-cache/                           |
|  - .deptrac-cache/                           |
|  - Docker BuildKit layers                    |
|                                              |
|  Weekly cron: prune old worktrees + images   |
+---------------------------------------------+
```
