<?php
namespace Ovi\Instagram\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 *
 * @package Ovi\Instagram\Helper
 */
class Data extends AbstractHelper
{
    const INSTAGRAM_CONFIG_ENABLE = 'instagram_section/instagram_general/enable';
    const INSTAGRAM_CONFIG_CLIENT_ID = 'instagram_section/instagram_general/client_id';
    const INSTAGRAM_CONFIG_CLIENT_SECRET = 'instagram_section/instagram_general/client_secret';
    const INSTAGRAM_CONFIG_USERNAME = 'instagram_section/instagram_general/username';
    const INSTAGRAM_CONFIG_REDIRECT_URL = 'instagram_section/instagram_general/redirect_url';


    /**
     * Data constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Get User ID by Username use in Instagram page
     *
     * @return null
     */
    public function getUserIdByUsername()
    {
        $username = $this->getConfig(self::INSTAGRAM_CONFIG_USERNAME);

        if ($username) {

            $url = 'https://www.instagram.com/' . $username . '/?__a=1';

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

            $result = curl_exec($ch);
            curl_close($ch);


            $data = json_decode($result, true);

            if (isset($data['user']['id'])) {
                return $data['user']['id'];
            }
        }

        return null;
    }

    /**
     * Return value config in admin by key
     *
     * @param $key
     *
     * @return mixed
     */
    public function getConfig($key)
    {
        $result = $this->scopeConfig->getValue($key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $result;
    }

    /**
     * Get config Enable data
     *
     * @return mixed
     */
    public function getInstaEnable()
    {
        return $this->getConfig(self::INSTAGRAM_CONFIG_ENABLE);
    }

    /**
     * Get Client ID config data
     *
     * @return mixed
     */
    public function getInstaClientID()
    {
        return $this->getConfig(self::INSTAGRAM_CONFIG_CLIENT_ID);
    }

    /**
     * Get Client Secret Config data
     *
     * @return mixed
     */
    public function getInstaClientSecret()
    {
        return $this->getConfig(self::INSTAGRAM_CONFIG_CLIENT_SECRET);
    }

    /**
     * Get redirect url config data (callback url)
     *
     * @return mixed
     */
    public function getInstaCallbackUrl()
    {
        return $this->getConfig(self::INSTAGRAM_CONFIG_REDIRECT_URL);
    }

    /**
     * Get Username of
     *
     * @return mixed
     */
    public function getInstaUsername()
    {
        return $this->getConfig(self::INSTAGRAM_CONFIG_USERNAME);
    }
}