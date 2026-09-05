<?php

namespace Concrete\Package\AltchaCaptcha;

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Captcha\Library as CaptchaLibrary;
use Concrete\Core\Entity\Package as PackageEntity;
use Concrete\Core\Package\Package;

class Controller extends Package
{
    protected $pkgHandle = 'altcha_captcha';
    protected $pkgVersion = '1.1.3';
    protected $appVersionRequired = '9.0.0';
    protected $phpVersionRequired = '8.1';

    protected $pkgAutoloaderRegistries = [
        'src/' => 'Concrete\\Package\\AltchaCaptcha',
    ];

    public function getPackageName()
    {
        return t('ALTCHA CAPTCHA');
    }

    public function getPackageDescription()
    {
        return t('Self-hosted, privacy-friendly proof-of-work bot protection for Concrete CMS.');
    }

    public function on_start()
    {
        $this->loadVendorAutoloader();
        $this->registerAssets();

        $router = $this->app->make('router');
        (new RouteList())->loadRoutes($router);
    }

    public function install()
    {
        $this->requireAltchaDependency();

        $pkg = parent::install();

        $this->ensureSecret();
        $this->ensureDatabaseTables();
        $this->ensureCaptchaLibrary($pkg);
        $this->registerAssets();

        return $pkg;
    }

    public function upgrade()
    {
        $this->requireAltchaDependency();
        parent::upgrade();

        $this->ensureSecret();
        $this->ensureDatabaseTables();

        $pkg = $this->getPackageEntity();
        if ($pkg !== null) {
            $this->ensureCaptchaLibrary($pkg);
        }
    }

    public function uninstall()
    {
        parent::uninstall();

        $db = $this->app->make('database')->connection();
        foreach (['AltchaCaptchaRateLimits', 'AltchaCaptchaUsedChallenges'] as $table) {
            if ($db->tableExists($table)) {
                $db->executeStatement('DROP TABLE ' . $table);
            }
        }
    }

    protected function registerAssets(): void
    {
        $assetList = AssetList::getInstance();

        $assetList->register(
            'javascript',
            'altcha',
            'js/altcha.js',
            [
                'position' => Asset::ASSET_POSITION_FOOTER,
                'version' => $this->pkgVersion,
            ],
            $this
        );

        $assetList->register(
            'css',
            'altcha',
            'css/altcha.css',
            [
                'position' => Asset::ASSET_POSITION_FOOTER,
                'version' => $this->pkgVersion,
            ],
            $this
        );
    }


    private function ensureDatabaseTables(): void
    {
        $db = $this->app->make('database')->connection();

        if (!$db->tableExists('AltchaCaptchaUsedChallenges')) {
            $db->executeStatement(
                'CREATE TABLE AltchaCaptchaUsedChallenges ('
                . 'challengeHash VARCHAR(64) NOT NULL, '
                . 'expiresAt INT UNSIGNED NOT NULL, '
                . 'PRIMARY KEY (challengeHash), '
                . 'INDEX idxAltchaCaptchaExpiresAt (expiresAt)'
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );
        }

        if (!$db->tableExists('AltchaCaptchaRateLimits')) {
            $db->executeStatement(
                'CREATE TABLE AltchaCaptchaRateLimits ('
                . 'rateKey VARCHAR(64) NOT NULL, '
                . 'requestCount INT UNSIGNED NOT NULL DEFAULT 0, '
                . 'expiresAt INT UNSIGNED NOT NULL, '
                . 'PRIMARY KEY (rateKey), '
                . 'INDEX idxAltchaCaptchaRateExpiresAt (expiresAt)'
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );
        }
    }

    private function ensureSecret(): void
    {
        $config = $this->getConfig();
        $secret = (string) $config->get('settings.hmac_key', '');

        if (!preg_match('/^[a-f0-9]{64}$/i', $secret)) {
            $config->save('settings.hmac_key', bin2hex(random_bytes(32)));
        }
    }

    private function ensureCaptchaLibrary(PackageEntity $pkg): void
    {
        if (!CaptchaLibrary::getByHandle('altcha')) {
            CaptchaLibrary::add('altcha', t('ALTCHA CAPTCHA'), $pkg);
        }
    }

    private function loadVendorAutoloader(): void
    {
        if (class_exists('AltchaOrg\\Altcha\\Altcha')) {
            return;
        }

        $vendorAutoloader = __DIR__ . '/vendor/autoload.php';
        if (is_file($vendorAutoloader)) {
            require_once $vendorAutoloader;
        }
    }

    private function requireAltchaDependency(): void
    {
        $this->loadVendorAutoloader();

        if (!class_exists('AltchaOrg\\Altcha\\Altcha')) {
            throw new \RuntimeException(
                t('ALTCHA PHP dependency is missing. Run "composer install --no-dev" inside the package directory before installing or upgrading the package.')
            );
        }
    }
}
