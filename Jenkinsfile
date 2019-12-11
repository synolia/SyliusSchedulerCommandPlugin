#!/usr/bin/env groovy

@Library("groovyFramework")

import com.synolia.log.Slack;
import com.synolia.system.Security;
import com.synolia.quality.PhpStan;

// Global
projectName = "Sylius Scheduler Plugin"
srcDir = "src"
binDir = "bin"
applicationDir = "tests/Application"
projectChannelName = "sylius-demo"
failedStage = "unknown"

// Docker
phpDockerRegistry = "registry.synolia.com/php72dev:latest"
dbDockerRegistry = "registry.synolia.com/percona57dev:latest"
seleniumRegistry = "selenium/standalone-chrome:3.141.59-europium"

// Get Slack Client
def slack = new Slack(this, "synolia", projectChannelName)
// Get Security tools
def security = new Security(this)

BUILD_TAG = env.BUILD_TAG.replaceAll('%2F', '_')
JOB_NAME = env.JOB_NAME.replaceAll('%2F', '/')
DB_URL = "mysql://synbshop:Synolia01@db-" + BUILD_TAG + ":3306/sylius-scheduler-plugin"
COMPOSER_ARGS = '-e COMPOSER_HOME=$HOME/.composer -v $HOME/.composer:$HOME/.composer'
PHP_BUILD_ARGS = '--name=php-' + BUILD_TAG

pipeline {
    agent any
    options {
        durabilityHint('PERFORMANCE_OPTIMIZED')
    }
    stages {
        stage('Creating Selenium container') {
            steps {
                script {
                    try {
                        echo "Installing Selenium container"
                        selenium = docker.image(seleniumRegistry)
                        // Use the same name for each process: "selenium_chrome" because we don't need multiple seleniums, we can keep it !
                        // I don't remove it cause it could be use by several apps...
                        selenium.run('--name selenium_chrome --volume /dev/shm:/dev/shm -p 4444:4444')
                    } catch (Exception e) {
                        echo "Container probably already exists... but it's ok ;)"
                        echo e.getMessage()
                    }
                }
            }
        }
        stage('Creating MySQL container') {
            steps {
                script {
                    db = docker.image(dbDockerRegistry)
                    db.run('--name db-'+BUILD_TAG)
                }
            }
            post { unsuccessful { script { failedStage = env.STAGE_NAME } } }
        }
        stage('Create Php Container') {
            agent {
                docker {
                    image phpDockerRegistry
                    args "${PHP_BUILD_ARGS} ${COMPOSER_ARGS} --link selenium_chrome:selenium_chrome --link db-${BUILD_TAG}:db -e APP_ENV=test"
                    reuseNode true
                }
            }
            stages {
                stage('Preparing tools') {
                    steps {
                        script {
                            security.buildSshFolder()

                            // Credentials for Github
                            withCredentials([string(credentialsId: 'githubToken', variable: 'githubToken')]) {
                                sh "composer config -g github-oauth.github.com ${githubToken}"
                            }

                            // Credentials for BitBucket
                            withCredentials([usernamePassword(credentialsId: 'bitbucketToken', passwordVariable: 'token', usernameVariable: 'consumerKey')]) {
                                sh "composer config -g bitbucket-oauth.bitbucket.org ${consumerKey} ${token}"
                            }

                            // Composer install package as global
                            sh 'composer global require hirak/prestissimo ^0.3'
                        }
                    }
                    post { unsuccessful { script { failedStage = env.STAGE_NAME } } }
                }

                stage('Application Installation') {
                    steps {
                        script {
                            // Set database to .env
                            sh "echo DATABASE_URL=${DB_URL} >> ${applicationDir}/.env.test"

                            // Composer install
                            sshagent(['deploy_symfony']) {
                                sh "php -d memory_limit=-1 /usr/local/bin/composer install --no-interaction --prefer-dist"
                            }

                            sh "cp behat.yml.dist behat.yml"
                            sh "sed -i 's/localhost:8080/selenium_chrome:4444/g' behat.yml"
                            sh "sed -i 's|{{DB_URL}}|${DB_URL}|g' phpunit.xml.dist"
                            sh "cd ${applicationDir}; yarn install && yarn build"
                            sh "cd ${applicationDir}; php bin/console doctrine:database:create --env=test"
                            sh "cd ${applicationDir}; php bin/console doctrine:schema:create --env=test"
                            sh "cd ${applicationDir}; php bin/console assets:install public --symlink"
                            sh "cd ${applicationDir}; php bin/console cache:warmup --env=test"
                        }
                    }
                    post { unsuccessful { script { failedStage = env.STAGE_NAME } } }
                }

                stage('Quality Tools') {
                    parallel {
                        stage('Composer Validation') {
                            steps {
                                script {
                                    sh "composer validate"
                                }
                            }
                            post { unsuccessful { script { failedStage = env.STAGE_NAME } } }
                        }

                        stage('PhpStan') {
                            steps {
                                script {
                                    def phpStan = new PhpStan(this, 'vendor/bin/phpstan')
                                    phpStan.runOnDirectory(
                                        srcDir,
                                        "."
                                    )
                                }
                            }
                            post { unsuccessful { script { failedStage = env.STAGE_NAME } } }
                        }
                    }
                }

                stage('Testing') {
                    parallel {
                        stage('PhpUnit') {
                            steps {
                                script {
                                    try {
                                        sh """
                                            APP_ENV=test \
                                            vendor/bin/phpunit -c phpunit.xml.dist \
                                                --log-junit phpunit-junit.xml
                                        """
                                    } finally {
                                         junit "phpunit-junit.xml"
                                    }
                                }
                            }
                            post { unsuccessful { script { failedStage = env.STAGE_NAME } } }
                        }

                        stage('PhpSpec') {
                            steps {
                                script {
                                    sh "vendor/bin/phpspec run --no-code-generation --format=junit > junit_phpspec.xml"
                                    junit "junit_phpspec.xml"
                                }
                            }
                            post { unsuccessful { script { failedStage = env.STAGE_NAME } } }
                        }

                        stage('Behat') {
                            steps {
                                script {
                                    sh "vendor/bin/behat -f pretty -o pretty.out -f progress -o std -f junit -o testreports"
                                    junit "testreports/*.xml"
                                }
                            }
                            post { unsuccessful { script { failedStage = env.STAGE_NAME } } }
                        }
                    }
                }
            }
        }
    }
    post {
        success {
            script {
                def message = "SUCCESS :champagne: \n Build <${env.RUN_DISPLAY_URL}|#${env.BUILD_NUMBER}> >> ${JOB_NAME}."
                if (env.CHANGE_URL) {
                    message = message + "\n <${env.CHANGE_URL}|${env.BRANCH_NAME} (${CHANGE_BRANCH})> Let's go for code review."
                }

                slack.send(message, "#14892c")
            }
        }
        unsuccessful {
            script {
                def message = ":warning: ${currentBuild.result}: \n Build <${env.RUN_DISPLAY_URL}|#${env.BUILD_NUMBER}> failed at stage *${failedStage}* >> ${JOB_NAME}."
                if (env.CHANGE_URL) {
                    message = message + "\n <${env.CHANGE_URL}|${env.BRANCH_NAME} (${CHANGE_BRANCH})> needs to be fixed."
                }
                slack.send(message, "#e01716")
            }
        }
        always {
            cleanWs deleteDirs: true, notFailBuild: true
            sh 'docker stop --time=1 db-' + BUILD_TAG + ' || true'
            sh 'docker rm -f db-' + BUILD_TAG + ' || true'
        }
    }
}
