#!/bin/bash

set -E -o pipefail
set -x

############################################################
# Prepare                                                  #
############################################################

# == Environment variable setting example ==
# -- required --
# export JENKINS_WORKSPACE=/data/jenkins_test/workspace/ad_monster_develop_web
# export ADMONSTER_WORKSPACE=/data/admonster_web
# export BRANCH=develop
# export ENV=develop
# -- elective --
# export TARGET_GROUP_ARN=
# export INSTANCE_ID=

# Initialize variables
commit_message=
branch_name=
business_id=
before_commit_id=
after_commit_id=
rollback_flg=0
today=`date "+%Y/%m/%d"`

############################################################
# Function                                                 #
############################################################

function docker_exec() {
  eval docker exec laradock_workspace_1 '$1'
}

function get_branch_name() {
  if [ -n "$commit_message" -a "`echo $commit_message | grep 'Merge pull request'`" ]; then
    local arr=($commit_message)
    echo ${arr[4]}
  fi
  echo ""
}

function get_business_id() {
  if [ -n "$branch_name" -a "`echo $branch_name | grep 'biz/'`" ]; then
    echo ${branch_name#biz/}
  fi
  echo ""
}

function prepare() {
  # Deregisters the specified targets from the specified target group
  if [ -n "$INSTANCE_ID" -a -n "$TARGET_GROUP_ARN" ]; then
    aws elbv2 deregister-targets --target-group-arn $TARGET_GROUP_ARN --targets Id=$INSTANCE_ID,Port=80
  fi
}

function check() {
  # Check changes in the business development branch
  if [ -n "$business_id" ]; then
    bash ${ADMONSTER_WORKSPACE}/admonster_app/file_check.sh ${business_id} ${BRANCH}
  fi
}

function test_static_analysis() {
  # PHP_CodeSniffer
  docker_exec './vendor/bin/phpcs -n --colors --standard=php_codesniffer_rule.xml ./'
  # Larastan
  docker_exec './vendor/bin/phpstan analyse'
  # ESLint
  docker_exec 'npm run eslint'
  # phpunit
  docker_exec './vendor/bin/phpunit --testdox'
}

function build() {
  # Autoload classmap
  docker_exec 'composer dump-autoload'

  # Clear caches
  docker_exec 'php artisan cache:clear'

  # Clear and cache config
  docker_exec 'php artisan config:clear'
  docker_exec 'php artisan config:cache'

  # Clear and cache routes
  docker_exec 'php artisan route:clear'
  # TODO: https://qiita.com/shohei_ot/items/3a2ce550cdfecb48acf5
  # php artisan route:cache

  # Clear and cache views
  docker_exec 'php artisan view:clear'
  docker_exec 'php artisan view:cache'

  # Generate const and vue-i18n
  docker_exec 'php artisan const:generate'
  docker_exec 'php artisan vue-i18n:generate'

  # Install node modules
  docker_exec 'npm ci --unsafe-perm'

  # Install/update composer dependecies
  # Build assets using Laravel Mix
  if [ "production" = ${ENV} -o "staging" = ${ENV} ]; then
    docker_exec 'composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev'
    docker_exec 'npm run prod'
  else
    docker_exec 'composer install --no-interaction --prefer-dist --optimize-autoloader'
    docker_exec 'npm run dev'
  fi

  # Run database migrations
  docker_exec 'php artisan migrate --force'
}

function run () {
  # Nothing
  return 0
}

function rollback() {
  # Rollback git
  git reset --hard $before_commit_id
  git push -f origin $BRANCH

  # Rebuild
  build
}

############################################################
# Error handling                                           #
############################################################

function raise() {
  echo $1 1>&2
  return 1
}

err_buf=""
function err() {
  # Usage: trap 'err ${LINENO[0]} ${FUNCNAME[1]}' ERR
  status=$?
  lineno=$1
  func_name=${2:-main}
  err_str="[ERROR]: [`date +'%Y-%m-%d %H:%M:%S'`] ${SCRIPT}:${func_name}() returned non-zero exit status ${status} at line ${lineno}"
  echo ${err_str}
  err_buf+=${err_str}

  if [ $rollback_flg -gt 0 ]; then
    rollback
  fi

  exit 1
}

function finally() {
  status=$?
  # Only if status is successful
  if [ 0 -eq ${status} ]; then
    # Registers the specified targets from the specified target group
    if [ -n "$INSTANCE_ID" -a -n "$TARGET_GROUP_ARN" ]; then
      aws elbv2 register-targets --target-group-arn $TARGET_GROUP_ARN --targets Id=$INSTANCE_ID,Port=80
    fi
  fi

  # Disable error handling
  set +e
  # Prune docker images and volumes
  docker image prune --force
  docker volume prune --force
}

trap 'err ${LINENO[0]} ${FUNCNAME[1]}' ERR
trap finally EXIT

############################################################
# Main                                                     #
############################################################

##############################
# JENKINS_WORKSPACE
cd $JENKINS_WORKSPACE

# Prepare for deployment
prepare

readonly commit_message=`git log --pretty=format:"%s" -1`
echo "[INFO]: commit_message: ${commit_message}"

readonly branch_name=`get_branch_name "${commit_message}"`
echo "[INFO]: branch_name: ${branch_name}"

readonly business_id=`get_business_id "${branch_name}"`
echo "[INFO]: business_id: ${business_id}"

readonly after_commit_id=`git rev-parse --short HEAD`
echo "[INFO]: after_commit_id: ${after_commit_id}"

# Check changes
if [ "production" = ${ENV} -o "staging" = ${ENV} ]; then
  echo "[INFO]: pass 'check' function"
else
  check
fi

##############################
# ADMONSTER_WORKSPACE
cd $ADMONSTER_WORKSPACE

readonly before_commit_id=`git rev-parse --short HEAD`
echo "[INFO]: before_commit_id: ${before_commit_id}"

# Pull the latest changes from the git repository
git fetch
git checkout ${BRANCH}
git pull origin ${BRANCH}

# After that, rollback git commit if an error occurs
if [ "production" = ${ENV} -o "staging" = ${ENV} ]; then
  echo "[INFO]: pass rollback"
else
  rollback_flg=1
fi

# Build with latest code
build

# Run docker images
run

# Test with latest code
if [ "production" = ${ENV} -o "staging" = ${ENV} ]; then
  echo "[INFO]: pass 'test_static_analysis' function"
else
  test_static_analysis
fi
