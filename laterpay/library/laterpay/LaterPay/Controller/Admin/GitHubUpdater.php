<?php

class LaterPay_Controller_Admin_GitHubUpdater
{

    const GITHUB_API_URL = 'https://api.github.com';

    /**
     * GitHub username
     * @var string
     */
    private $username;

    /**
     * GitHub repo name
     * @var string
     */
    private $repo;

    /**
     * holds data from GitHub
     * @var array
     */
    private $githubAPIResult;

    /**
     * GitHub private repo token
     * @var string
     */
    private $accessToken;

    /**
     * @var bool
     */
    private $wasActivated = false;

    /**
     * @var LaterPay_Model_Config
     */
    private $config;

    public function __construct( LaterPay_Model_Config $config ) {
        $this->config = $config;

        $this->username     = $this->config->get( 'github.user' );
        $this->repo         = $this->config->get( 'github.name' );
        $this->accessToken  = $this->config->get( 'github.token' );
    }

    /**
     * Get information regarding our plugin from GitHub.
     *
     * @return void
     */
    private function get_repo_release_info() {
        // only do this once
        if ( ! empty( $this->githubAPIResult ) ) {
            return;
        }

        // query the GitHub API
        $url = self::GITHUB_API_URL . '/repos/' . $this->username . '/' . $this->repo . '/releases';

        // we need the access token for private repos
        if ( ! empty( $this->accessToken ) ) {
            $url = add_query_arg( array( 'access_token' => $this->accessToken ), $url );
        }

        // get the results
        $result = wp_remote_get( $url );
        $this->githubAPIResult = wp_remote_retrieve_body( $result );
        if ( ! empty( $this->githubAPIResult ) ) {
            $this->githubAPIResult = @json_decode( $this->githubAPIResult );
        }

        // use only the latest release
        if ( is_array( $this->githubAPIResult ) ) {
            $this->githubAPIResult = $this->githubAPIResult[0];
        }
    }

    /**
     * Push in plugin version information to get the update notification.
     *
     * @param object $transient
     *
     * @return object
     */
    public function set_transient( $transient ) {

        // if we have checked the plugin data before, don't re-check
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        // get plugin and GitHub release information
        $this->get_repo_release_info();

        $plugin_base_name = $this->config->get( 'plugin_base_name' );

        if ( !array_key_exists( $plugin_base_name, $transient->checked  ) ) {
            return $transient;
        }

        // check the versions, if we need to do an update
        $doUpdate = version_compare(
            substr( $this->githubAPIResult->tag_name, 1 ),
            $transient->checked[ $plugin_base_name ]
        );

        // update the transient to include our updated plugin data
        if ( $doUpdate == 1 ) {
            $package = $this->githubAPIResult->zipball_url;

            // include the access token for private GitHub repos
            if ( ! empty($this->accessToken) ) {
                $package = add_query_arg( array( 'access_token' => $this->accessToken ), $package );
            }

            $obj                = new stdClass();
            $obj->slug          = $this->config->get( 'plugin_base_name' );
            $obj->new_version   = substr( $this->githubAPIResult->tag_name, 1 );
            $obj->url           = $this->config->get( 'plugin_uri' );
            $obj->package       = $package;
            $transient->response[ $plugin_base_name ] = $obj;
        }

        return $transient;
    }

    /**
     * Push in plugin version information to display in the details lightbox.
     *
     * @param bool   $false
     * @param string $action
     * @param object $response
     *
     * @return type
     */
    public function set_plugin_info( $false, $action, $response ) {

        // get plugin and GitHub release information
        $this->get_repo_release_info();
        // if nothing is found, do nothing
        if ( empty( $response->slug ) || $response->slug != $this->config->get( 'plugin_base_name' ) ) {
            return false;
        }
        // add our plugin information
        $response->last_updated = $this->githubAPIResult->published_at;
        $response->slug         = $this->config->get( 'plugin_basename' );
        $response->plugin_name  = $this->config->get( 'name' );
        $response->version      = substr($this->githubAPIResult->tag_name, 1);
        $response->author       = $this->config->get( 'author' );
        $response->homepage     = $this->config->get( 'plugin_uri' );

        // this is our release download zip file
        $downloadLink = $this->githubAPIResult->zipball_url;

        // include the access token for private GitHub repos
        if ( ! empty( $this->accessToken ) ) {
            $downloadLink = add_query_arg( array( 'access_token' => $this->accessToken ), $downloadLink );
        }
        $response->download_link = $downloadLink;
        // create tabs in the lightbox
        if ( class_exists( 'Parsedown' ) ) {
            $changelog = Parsedown::instance()->parse( $this->githubAPIResult->body );
        } else {
            $changelog = $this->githubAPIResult->body;
        }
        $response->sections = array (
            'description'   => $this->config->get( 'description' ),
            'changelog'     => $changelog,
        );

        // get the required version of WordPress if available
        $matches = null;
        preg_match( '/requires:\s([\d\.]+)/i', $this->githubAPIResult->body, $matches );
        if ( ! empty( $matches ) ) {
            if ( is_array( $matches ) ) {
                if ( count( $matches ) > 1 ) {
                    $response->requires = $matches[1];
                }
            }
        }

        // get the tested version of WordPress if available
        $matches = null;
        preg_match( '/tested:\s([\d\.]+)/i', $this->githubAPIResult->body, $matches );
        if ( ! empty( $matches ) ) {
            if ( is_array( $matches ) ) {
                if ( count( $matches ) > 1 ) {
                    $response->tested = $matches[1];
                }
            }
        }

        return $response;
    }

    /**
     * Perform additional actions to successfully install our plugin.
     *
     * @param bool   $true
     * @param string $hook_extra
     * @param array  $result
     *
     * @return array
     */
    public function post_install( $true, $hook_extra, $result ) {
        global $wp_filesystem;
#
        $slug = $this->config->get( 'plugin_base_name' );

        // since our plugin is hosted on GitHub, our plugin folder would have a dirname of
        // reponame-tagname, so we have to change it to our original one:
        $pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname( $slug );

        $wp_filesystem->move( $result['destination'] . DIRECTORY_SEPARATOR . dirname( $slug ), $pluginFolder );
        $wp_filesystem->delete( $result['destination'], true );
        $result['destination'] = $pluginFolder;
        // restore config file
        $this->backup_config( true );
        // re-activate plugin, if needed
        if ( $this->wasActivated ) {
            $activate = activate_plugin( $slug );
        }

        return $result;
    }

    private function backup_config( $restore = false, $file = 'settings' ) {
        global $wp_filesystem;

        $slug = $this->config->get( 'plugin_base_name' );

        // back up config file, if it exists
        $pluginFolder   = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname( $slug );
        $configName     = $file . '.php';
        $configFile     = $pluginFolder . DIRECTORY_SEPARATOR . $configName;
        $backup         = $pluginFolder . '_' . $configName . '.backup';

        if ( ! $restore && file_exists( $configFile ) ) {
            $wp_filesystem->copy( $configFile, $backup, true );
        } else if ( $restore && file_exists( $backup ) ) {
            $wp_filesystem->move( $backup, $configFile, true );
        }
    }

    /**
     * Perform additional actions to successfully install our plugin.
     *
     * @param $return
     * @param $plugin
     *
     * @return array
     */
    public function pre_install( $return, $plugin ) {

        $plugin = isset( $plugin['plugin'] ) ? $plugin['plugin'] : '';

        if ( empty( $plugin ) || $plugin != $this->config->get( 'plugin_base_name' ) ) {
            return;
        }

        // remember, if our plugin was previously activated
        $this->wasActivated = is_plugin_active( $this->config->get( 'plugin_base_name' ) );

        $this->backup_config();
    }

}
