<?php
defined('C5_EXECUTE') or die('Access Denied.');

$form = app()->make('helper/form');
$config = app()->make('config');
$current = Config::get('altcha_captcha.settings.hmac_key');

//$current = Config::get('altcha_captcha.settings.hmac_key');
?>
<div class="alert alert-info">
    <?= t('A site key and secret key must be provided.') ?>
</div>

<div class="form-group">
    <?= $form->label('hmac_key', t('HMAC Key')) ?>
    <?= $form->text('hmac_key', $current, ['maxlength' => 64, 'placeholder' => t('64-char hex key'), 'required' => true]) ?>
    <small class="form-text text-muted">
        <?= t('Must be 64 hexadecimal characters (256-bit key).') ?><br />
        <?= t('You can generate one') ?>
        <?php echo '<a href="https://codebeautify.org/hmac-generator" target="_blank">' . t('using this secure HMAC generator') . '</a>'?>
        <?= t('or from your terminal:') ?>
        <code><?= t('openssl rand -hex 32') ?></code>
    </small>
</div>
