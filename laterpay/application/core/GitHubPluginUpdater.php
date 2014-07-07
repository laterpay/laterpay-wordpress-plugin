<?php

class GitHubPluginUpdater {

    const GITHUB_API_URL = 'https://api.github.com';

    private $slug;                   // plugin slug
    private $pluginData;             // plugin data
    private $username;               // GitHub username
    private $repo;                   // GitHub repo name
    private $pluginFile;             // __FILE__ of our plugin
    private $githubAPIResult;        // holds data from GitHub
    private $accessToken;            // GitHub private repo token
    private $wasActivated = false;

    /**
     * GitHubPluginUpdater
     *
     * @param type $pluginFile
     * @param type $gitHubUsername
     * @param type $gitHubProjectName
     * @param type $accessToken
     */
    public function init( $pluginFile, $gitHubUsername, $gitHubProjectName, $accessToken = '' ) {
        $this->pluginFile   = $pluginFile;
        $this->username     = $gitHubUsername;
        $this->repo         = $gitHubProjectName;
        $this->accessToken  = $accessToken;
    }

    /**
     * Get information regarding our plugin from WordPress
     *
     */
    private function initPluginData() {
        $this->slug = plugin_basename($this->pluginFile);
        $this->pluginData = get_plugin_data($this->pluginFile);
    }

    /**
     * Get information regarding our plugin from GitHub
     *
     * @return void
     */
    private function getRepoReleaseInfo() {
        // only do this once
        if ( !empty($this->githubAPIResult) ) {
            return;
        }
        // query the GitHub API
        $url = self::GITHUB_API_URL . '/repos/' . $this->username . '/' . $this->repo . '/releases';

        // we need the access token for private repos
        if ( !empty($this->accessToken) ) {
            $url = add_query_arg(array('access_token' => $this->accessToken), $url);
        }

        // get the results
        $this->githubAPIResult = wp_remote_retrieve_body(wp_remote_get($url));
        if ( !empty($this->githubAPIResult) ) {
            $this->githubAPIResult = @json_decode($this->githubAPIResult);
        }

        // use only the latest release
        if ( is_array($this->githubAPIResult) ) {
            $this->githubAPIResult = $this->githubAPIResult[0];
        }
    }

    /**
     * Push in plugin version information to get the update notification
     *
     * @param type $transient
     *
     * @return type
     */
    public function setTransient( $transient ) {

        // if we have checked the plugin data before, don't re-check
        if ( empty($transient->checked) ) {
            return $transient;
        }

        // get plugin and GitHub release information
        $this->initPluginData();
        $this->getRepoReleaseInfo();

        // check the versions if we need to do an update
        $doUpdate = version_compare(substr($this->githubAPIResult->tag_name, 1), $transient->checked[$this->slug]);

        // update the transient to include our updated plugin data
        if ( $doUpdate == 1 ) {
            $package = $this->githubAPIResult->zipball_url;

            // include the access token for private GitHub repos
            if ( !empty($this->accessToken) ) {
                $package = add_query_arg(array('access_token' => $this->accessToken), $package);
            }

            $obj                = new stdClass();
            $obj->slug          = $this->slug;
            $obj->new_version   = substr($this->githubAPIResult->tag_name, 1);
            $obj->url           = $this->pluginData['PluginURI'];
            $obj->package       = $package;
            $transient->response[$this->slug] = $obj;
        }

        return $transient;
    }

    /**
     * Push in plugin version information to display in the details lightbox
     *
     * @param type $false
     * @param type $action
     * @param type $response
     *
     * @return type
     */
    public function setPluginInfo( $false, $action, $response ) {

        // get plugin and GitHub release information
        $this->initPluginData();
        $this->getRepoReleaseInfo();
        // if nothing is found, do nothing
        if ( empty($response->slug) || $response->slug != $this->slug ) {
            return false;
        }
        // add our plugin information
        $response->last_updated = $this->githubAPIResult->published_at;
        $response->slug         = $this->slug;
        $response->plugin_name  = $this->pluginData['Name'];
        $response->version      = substr($this->githubAPIResult->tag_name, 1);
        $response->author       = $this->pluginData['AuthorName'];
        $response->homepage     = $this->pluginData['PluginURI'];

        // this is our release download zip file
        $downloadLink = $this->githubAPIResult->zipball_url;

        // include the access token for private GitHub repos
        if ( !empty($this->accessToken) ) {
            $downloadLink = add_query_arg(
                array('access_token' => $this->accessToken), $downloadLink
            );
        }
        $response->download_link = $downloadLink;
        // create tabs in the lightbox
        if ( class_exists('Parsedown') ) {
            $changelog = Parsedown::instance()->parse( $this->githubAPIResult->body );
        } else {
            $changelog = $this->githubAPIResult->body;
        }
        $response->sections = array (
            'description'   => $this->pluginData['Description'],
            'changelog'     => $changelog
        );

        // get the required version of WP if available
        $matches = null;
        preg_match("/requires:\s([\d\.]+)/i", $this->githubAPIResult->body, $matches);
        if ( !empty($matches) ) {
            if ( is_array($matches) ) {
                if ( count($matches) > 1 ) {
                    $response->requires = $matches[1];
                }
            }
        }

        // get the tested version of WP if available
        $matches = null;
        preg_match("/tested:\s([\d\.]+)/i", $this->githubAPIResult->body, $matches);
        if ( !empty($matches) ) {
            if ( is_array($matches) ) {
                if ( count($matches) > 1 ) {
                    $response->tested = $matches[1];
                }
            }
        }

        return $response;
    }

    /**
     * Perform additional actions to successfully install our plugin
     *
     * @param type $true
     * @param type $hook_extra
     * @param type $result
     *
     * @return type
     */
    public function postInstall( $true, $hook_extra, $result ) {
        global $wp_filesystem;

        // since our plugin is hosted on GitHub, our plugin folder would have a dirname of
        // reponame-tagname, so we have to change it to our original one:
        $pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->slug);

        $wp_filesystem->move($result['destination'] . DIRECTORY_SEPARATOR . dirname( $this->slug ), $pluginFolder);
        $wp_filesystem->delete($result['destination'], true);
        $result['destination'] = $pluginFolder;
        // restore config file
        $this->backupConfig(true);
        // re-activate plugin if needed
        if ( $this->wasActivated ) {
            $activate = activate_plugin($this->slug);
        }

        return $result;
    }

    private function backupConfig( $restore = false, $file = 'settings' ) {
        global $wp_filesystem;

        // back up config file, if it exists
        $pluginFolder   = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->slug);
        $configName     = $file . '.php';
        $configFile     = $pluginFolder . DIRECTORY_SEPARATOR . $configName;
        $backup         = $pluginFolder . '_' . $configName . '.backup';

        if ( !$restore && file_exists($configFile) ) {
            $wp_filesystem->copy($configFile, $backup, true);
        } else if ( $restore && file_exists($backup) ) {
                $wp_filesystem->move($backup, $configFile, true);
            }
    }

    /**
     * Perform additional actions to successfully install our plugin
     *
     * @param type $true
     * @param type $hook_extra
     * @param type $result
     *
     * @return type
     */
    public function preInstall( $return, $plugin ) {
        $this->initPluginData();

        $plugin = isset($plugin['plugin']) ? $plugin['plugin'] : '';

        if ( empty($plugin) || $plugin != $this->slug ) {
            return;
        }

        // remember if our plugin was previously activated
        $this->wasActivated = is_plugin_active($this->slug);

        $this->backupConfig();
    }

}
