<?php
namespace Concrete\Package\AltchaCaptcha;

use Concrete\Core\Package\Package;
use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Captcha\Library as CaptchaLibrary;
use Concrete\Package\AltchaCaptcha\Src\Captcha\AltchaController;

class Controller extends Package
{
    protected $pkgHandle = 'altcha_captcha';
    protected $pkgVersion = '0.9.0';
    protected $appVersionRequired = '9.0.0';

    protected $pkgAutoloaderRegistries = [
        'src/Captcha' => 'Concrete\Package\AltchaCaptcha\Captcha',
    ];

    public function getPackageName()
    {
        return t('Altcha CAPTCHA');
    }

    public function getPackageDescription()
    {
        return t('Self-hosted, privacy-friendly CAPTCHA using Altcha.');
    }

    public function on_start()
    {
        $this->registerAssets();
    }

    protected function registerAssets()
    {
        $assetList = \AssetList::getInstance();

        $assetList->register(
            'javascript',
            'altcha',
            'js/altcha.js',
            ['position' => Asset::ASSET_POSITION_FOOTER],
            $this
        );

        $assetList->register(
            'javascript',
            'glue',
            'js/glue.js',
            [
                'position' => Asset::ASSET_POSITION_FOOTER,
                'depends' => ['altcha'],
            ],
            $this
        );

        $assetList->register(
            'css',
            'altcha',
            'css/altcha.css',
            ['position' => Asset::ASSET_POSITION_FOOTER],
            $this
        );
    }

    public function install()
    {
        $pkg = parent::install();
        CaptchaLibrary::add('altcha', t('Altcha Captcha'), $pkg);
        $this->registerAssets();
        return $pkg;
    }

    public function getPackageConfigNamespace(): string
    {
        return 'altcha';
    }

}
