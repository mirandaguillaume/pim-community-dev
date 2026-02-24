#!/bin/bash
# Setup script for a self-hosted GitHub Actions runner on Hetzner CCX33.
# Run as root: bash setup-runner.sh
#
# See .github/docs/self-hosted-runner-setup.md for the full guide.
set -euo pipefail

echo "=== System update ==="
apt-get update && apt-get upgrade -y
apt-get install -y \
  curl git jq unzip acl ca-certificates gnupg \
  make build-essential

echo "=== Install Docker (official repo) ==="
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
chmod a+r /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" > /etc/apt/sources.list.d/docker.list
apt-get update
apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin docker-buildx-plugin
apt-get install -y php8.3-cli php8.3-xml php8.3-mbstring

echo "=== Install Node.js 18 + yarn ==="
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs
npm install -g yarn

systemctl enable docker && systemctl start docker

# Symlink docker-compose v2 plugin as standalone command (expected by CI)
ln -sf /usr/libexec/docker/cli-plugins/docker-compose /usr/local/bin/docker-compose

echo "=== Create runner user ==="
id runner &>/dev/null || useradd -m -s /bin/bash -G docker runner
loginctl enable-linger runner

# Passwordless sudo (required by GitHub Actions workflows)
echo "runner ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/runner
chmod 440 /etc/sudoers.d/runner

echo "=== Pre-pull Docker images (persistent cache) ==="
docker pull mysql:8.0.30 &
docker pull elastic/elasticsearch:8.11.3 &
docker pull selenium/standalone-chrome-debug:3.141.59 &
docker pull minio/minio:RELEASE.2025-01-20T14-49-07Z &
docker pull google/cloud-sdk:506.0.0-emulators &
docker pull fsouza/fake-gcs-server:1.45 &
docker pull node:20 &
wait

echo "=== Install GitHub Actions Runner ==="
cd /home/runner
mkdir -p actions-runner && cd actions-runner

RUNNER_VERSION=$(curl -s https://api.github.com/repos/actions/runner/releases/latest \
  | jq -r .tag_name | sed 's/v//')
echo "Installing runner v${RUNNER_VERSION}"
curl -o actions-runner.tar.gz -L \
  "https://github.com/actions/runner/releases/download/v${RUNNER_VERSION}/actions-runner-linux-x64-${RUNNER_VERSION}.tar.gz"
tar xzf actions-runner.tar.gz && rm actions-runner.tar.gz
chown -R runner:runner /home/runner/actions-runner

echo "=== Setup pre-job hook (fix Docker root-owned files) ==="
cat > /home/runner/cleanup-workspace.sh << 'HOOK'
#!/bin/bash
# Docker containers run as root and leave files the runner can't clean up.
# This hook runs before each job so actions/checkout can do git clean.
if [ -n "${GITHUB_WORKSPACE:-}" ] && [ -d "${GITHUB_WORKSPACE}" ]; then
  sudo chown -R $(id -u):$(id -g) "${GITHUB_WORKSPACE}" 2>/dev/null || true
fi
HOOK
chmod +x /home/runner/cleanup-workspace.sh

echo "=== Setup weekly cleanup cron ==="
cat > /etc/cron.weekly/cleanup-runner << 'CRON'
#!/bin/bash
# Remove CI worktrees older than 7 days
find /home/runner/actions-runner/_work -maxdepth 2 -mtime +7 -type d -exec rm -rf {} + 2>/dev/null
# Prune unused Docker resources older than 7 days
docker system prune -f --filter "until=168h"
CRON
chmod +x /etc/cron.weekly/cleanup-runner

echo ""
echo "============================================"
echo " Server ready!"
echo ""
echo " Next steps:"
echo " 1. Go to GitHub > Settings > Actions > Runners > New self-hosted runner"
echo " 2. Copy the registration token"
echo " 3. Run:"
echo "    su - runner"
echo "    cd ~/actions-runner"
echo "    ./config.sh --url https://github.com/YOUR_USER/YOUR_REPO --token YOUR_TOKEN --name ci-runner-01 --labels self-hosted,linux,x64 --work _work"
echo "    echo 'ACTIONS_RUNNER_HOOK_JOB_STARTED=/home/runner/cleanup-workspace.sh' >> .env"
echo "    sudo ./svc.sh install runner"
echo "    sudo ./svc.sh start"
echo " 4. Set repository variable RUNNER_LABEL=self-hosted in GitHub Settings"
echo "============================================"
