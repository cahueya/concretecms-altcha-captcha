<?php
namespace Concrete\Package\AltchaCaptcha\Captcha;

use Log;
use Psr\Log\LogLevel;
use AltchaOrg\Altcha\Altcha;
use Concrete\Core\View\View;
use Concrete\Core\Http\Request;
use Concrete\Core\Logging\Channels;
use Concrete\Core\Http\Client\Client;
use AltchaOrg\Altcha\ChallengeOptions;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Logging\LoggerAwareTrait;
use Concrete\Core\Captcha\CaptchaInterface;
use Concrete\Core\Logging\LoggerAwareInterface;
use Concrete\Core\Controller\AbstractController;

class AltchaController extends AbstractController implements CaptchaInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
        parent::__construct();
    }

    public function getLoggerChannel()
    {
        return Channels::CHANNEL_SPAM;
    }

    public function display(array $options = []): string
    {
        $session = $this->app->make('session');
        $config = $this->app->make('config');

        $hmacKey = $config->get('altcha_captcha.settings.hmac_key');

        if (!$hmacKey) {
            echo '<div class="alert alert-warning">' . t('Altcha is not configured correctly.') . '<br/>' . t('The HMAC key must be a valid 64-character hexadecimal string.') . '</div>';
            return '';
        } else {

            $altcha = new \AltchaOrg\Altcha\Altcha($hmacKey);
            $challenge = $altcha->createChallenge(new ChallengeOptions(
                maxNumber: 50000,
                expires: (new \DateTimeImmutable())->add(new \DateInterval('PT30S'))
            ));
            
            $session->set('altcha_challenge', json_encode($challenge));
            
            $challengeJson = htmlspecialchars(json_encode($challenge), ENT_QUOTES, 'UTF-8');
            
            echo '<altcha-widget challengejson="' . $challengeJson . '" data-theme="light">';
            echo '<input type="hidden" name="altcha" id="altcha-hidden" />';
            echo '</altcha-widget>';
            
            View::getInstance()->requireAsset('javascript', 'altcha');
            View::getInstance()->requireAsset('javascript', 'glue');
            View::getInstance()->requireAsset('css', 'altcha');
            return '';
        }
    }

    public function label()
    {
        return '';
    }

    public function check(): bool
    {
    
        /** @var Request $request */
        $request = Request::getInstance(); // More reliable in Concrete

        $rawPayload = $request->get('altcha');

        if (is_array($rawPayload)) {
            $rawPayload = reset($rawPayload);
        }
    
        $session = $this->app->make('session');

        if (!$rawPayload) {
            Log::addWarning('[Altcha] Missing altcha payload.');
            return false;
        }

        $decoded = base64_decode($rawPayload);
        $payload = json_decode($decoded, true);

        if (!is_array($payload)) {
            Log::addWarning('[Altcha] Invalid base64 or JSON format.');
            return false;
        }

        $stored = $session->get('altcha_challenge');
        if (!is_string($stored)) {
            Log::addWarning('[Altcha] No challenge stored in session.');
            return false;
        }

        $challenge = json_decode($stored, true);
        if (!is_array($challenge)) {
            Log::addWarning('[Altcha] Failed to decode challenge JSON.');
            return false;
        }

        $hmacKey = $this->app->make('config')->get('altcha_captcha.settings.hmac_key');

        $altcha = new \AltchaOrg\Altcha\Altcha($hmacKey);

        $isValid = $altcha->verifySolution($payload, true, $challenge);

        $session->remove('altcha_challenge');

        if ($isValid) {

            } else {
                Log::addWarning('[Altcha] CAPTCHA verification failed.');
            }
        return $isValid;
    }


    public function showInput()
    {
        $config = $this->app->make('config');
        $hmacKey = $config->get('altcha_captcha.settings.hmac_key');
        if (!$hmacKey || strlen($hmacKey) !== 64) {
            return '<div class="alert alert-warning">' . t('Altcha is not configured correctly.') . '</div>';
        }
        return '';
    }

    public function saveOptions($data)
    {
        $session = $this->app->make('session');
        $hmac = $data['hmac_key'] ?? '';

        $config = $this->app->make('config');

        if (!preg_match('/^[a-f0-9]{64}$/i', $hmac)) {
            $session->getFlashBag()->add('error', t('The HMAC key must be a valid 64-character hexadecimal string.'));
            return;
        }
        $config->save('altcha_captcha.settings.hmac_key', strtolower($hmac));
    }
}
