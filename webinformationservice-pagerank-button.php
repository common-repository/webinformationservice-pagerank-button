<?php
/*
  Plugin Name: WebInformationService PageRank Button
  Plugin URI: http://www.webinformationservice.net
  Description: Shows the PageRank for the website of your blog
  Version: 1.0
  Author: webinformationservice
  Author URI: http://www.webinformationservice.net
  License: GNU LESSER GENERAL PUBLIC LICENSE (http://www.gnu.org/copyleft/lesser.html)
 */
class webinformationservice_pagerank_button {
    private $pluginId = 'webinformationservice_pagerank_button';
    private $i18n;

    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_uninstall_hook(__FILE__, array($this, 'uninstall'));

        $this->i18n = new webinformationservice_pagerank_button_i18n(substr(get_bloginfo('language'), 0, 2));
    }

    public function init() {
        wp_register_sidebar_widget($this->pluginId, $this->i18n->_('name'), array($this, 'sidebar'));
        wp_register_widget_control($this->pluginId, $this->i18n->_('name'), array($this, 'settings'));
    }

    public function sidebar() {
        $enteredTargetDomain = $this->getTargetDomain();
        $targetDomain = $this->getVerifiedTargetDomain();
        printf('<aside id="wis-pagerank-widget" class="widget">' .
               '<a title="%s" href="http://www.webinformationservice.%s/%s">' .
               '<img src="http://www.%s/widget/pagerank/%s?s=wp" alt="%s">' .
               '</a></aside>',
            $this->i18n->_("link_title", $enteredTargetDomain),
            $this->getTargetPortal(),
            $targetDomain,
            $this->i18n->_('domain'),
            $enteredTargetDomain,
            $this->i18n->_("link_title", $enteredTargetDomain)
        );
    }

    public function settings() {
        if (count($_POST) > 0) {
            if (isset($_POST['webinformationservice_pagerank_button_domain'])) {
                $domain = str_replace(" ", "", trim(htmlspecialchars($_POST['webinformationservice_pagerank_button_domain'])));
                update_option('webinformationservice_pagerank_button_domain', $domain);

                $r = json_decode(wp_remote_retrieve_body(wp_remote_get("http://www.webinformationservice.net/islive/$domain")), true);
                if ($r === null) {
                    update_option('webinformationservice_pagerank_button_target_portal', "net");
                    update_option('webinformationservice_pagerank_button_target_domain', "");
                } else {
                    update_option('webinformationservice_pagerank_button_target_portal', $r["portal"]);
                    update_option('webinformationservice_pagerank_button_target_domain', $r["domain"]);
                }
            }
        }
        ?>
        <p>
            <label for="webinformationservice_pagerank_button_domain"><?php echo $this->i18n->_("form.your_domain"); ?></label><br>
            <input type="text" id="webinformationservice_pagerank_button_domain" name="webinformationservice_pagerank_button_domain" value="<?php echo $this->getTargetDomain(); ?>">
        </p>
    <?php
    }

    public function uninstall() {
        delete_option('webinformationservice_pagerank_button_domain');
        delete_option('webinformationservice_pagerank_button_target_portal');
        delete_option('webinformationservice_pagerank_button_target_domain');
    }

    private function getTargetDomain() {
        $domain = get_option("webinformationservice_pagerank_button_domain");
        if (empty($domain)) {
            if (isset($_SERVER['HTTP_HOST'])) {
                $domain = $_SERVER['HTTP_HOST'];
            }
        }
        return $domain;
    }

    private function getTargetPortal() {
        return get_option('webinformationservice_pagerank_button_target_portal');
    }

    private function getVerifiedTargetDomain() {
        return get_option('webinformationservice_pagerank_button_target_domain');
    }

}

class webinformationservice_pagerank_button_i18n {
    private $lang;
    private $texts = array(
        "de" => array(
            "name" => "WebInformationService PageRank Button"
            , "domain" => "webinformationservice.net"
            , "link_title" => "PageRank für %s auf WebInformationService"
            , "form.your_domain" => "Ihre Domain:"
            , "form.pagerank_example" => "PageRank Beispiel"
        ),
        "en" => array(
            "name" => "WebInformationService PageRank Button"
            , "domain" => "webinformationservice.net"
            , "link_title" => "PageRank for %s on WebInformationService"
            , "form.your_domain" => "Your domain:"
            , "form.pagerank_example" => "PageRank example"
        )
    );

    public function __construct($lang) {
        $this->lang = $lang;
    }

    public function _($key) {
        $args = func_get_args();
        if (sizeof($args) > 1) {
            array_shift($args);
            return vsprintf($this->texts[$this->lang][$key], $args);
        } else {
            return $this->texts[$this->lang][$key];
        }
    }
}

$webinformationservice_pagerank_button = new webinformationservice_pagerank_button();
