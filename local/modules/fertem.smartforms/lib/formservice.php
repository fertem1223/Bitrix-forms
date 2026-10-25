<?php
namespace Fertem\SmartForms;

use Bitrix\Main\Application;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Web\HttpClient;

final class FormService
{
    public static function submit(array $form, array $input, array $server = []): array
    {
        $settings = is_array($form['SETTINGS']) ? $form['SETTINGS'] : [];
        if (!empty($input['website'])) { return ['ok' => false, 'silent' => true]; }
        $started = (int)($input['_started'] ?? 0);
        if ($started && time() - $started < (int)($settings['min_seconds'] ?? 2)) {
            return ['ok' => false, 'errors' => ['Форма отправлена слишком быстро.']];
        }
        $ip = (string)($server['REMOTE_ADDR'] ?? '');
        $ipHash = hash('sha256', $ip . '|' . Application::getInstance()->getContext()->getServer()->getDocumentRoot());
        $limit = max(1, (int)($settings['rate_limit'] ?? 5));
        $since = new \Bitrix\Main\Type\DateTime();
        $since->add('-10 minutes');
        $recent = SubmissionTable::getCount(['=FORM_ID' => $form['ID'], '=IP_HASH' => $ipHash, '>=DATE_CREATE' => $since]);
        if ($recent >= $limit) { return ['ok' => false, 'errors' => ['Слишком много отправок. Попробуйте позже.']]; }

        $clean = []; $errors = [];
        foreach ((array)$form['FIELDS'] as $field) {
            $code = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($field['code'] ?? ''));
            if ($code === '') { continue; }
            $value = is_array($input[$code] ?? null) ? implode(', ', $input[$code]) : trim((string)($input[$code] ?? ''));
            if (($field['required'] ?? 'N') === 'Y' && $value === '') { $errors[$code] = 'Заполните поле «' . $field['label'] . '».'; }
            if (($field['type'] ?? '') === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) { $errors[$code] = 'Укажите корректный email.'; }
            $clean[$code] = mb_substr(strip_tags($value), 0, 5000);
        }
        if ($errors) { return ['ok' => false, 'errors' => $errors]; }
        $meta = [
            'page' => mb_substr((string)($input['_page'] ?? ''), 0, 1000),
            'referer' => mb_substr((string)($server['HTTP_REFERER'] ?? ''), 0, 1000),
            'user_agent' => mb_substr((string)($server['HTTP_USER_AGENT'] ?? ''), 0, 1000),
            'utm_source' => mb_substr((string)($input['_utm_source'] ?? ''), 0, 255),
            'utm_medium' => mb_substr((string)($input['_utm_medium'] ?? ''), 0, 255),
            'utm_campaign' => mb_substr((string)($input['_utm_campaign'] ?? ''), 0, 255),
        ];
        $result = SubmissionTable::add(['FORM_ID' => $form['ID'], 'DATA' => $clean, 'META' => $meta, 'IP_HASH' => $ipHash]);
        if (!$result->isSuccess()) { return ['ok' => false, 'errors' => $result->getErrorMessages()]; }
        $submissionId = $result->getId();
        self::notify($form, $clean, $meta, $submissionId);
        self::webhook($form, $clean, $meta, $submissionId);
        return ['ok' => true, 'id' => $submissionId, 'message' => $settings['success_message'] ?? 'Спасибо! Заявка отправлена.'];
    }

    private static function notify(array $form, array $data, array $meta, int $id): void
    {
        $emails = trim((string)($form['SETTINGS']['emails'] ?? ''));
        if ($emails === '') { return; }
        $rows = [];
        foreach ($data as $key => $value) { $rows[] = $key . ': ' . $value; }
        Event::sendImmediate(['EVENT_NAME' => 'FERTEM_SMARTFORM_NEW', 'LID' => SITE_ID, 'C_FIELDS' => [
            'EMAIL_TO' => $emails, 'FORM_NAME' => $form['NAME'], 'SUBMISSION_ID' => $id,
            'FIELDS' => implode("\n", $rows), 'PAGE' => $meta['page'],
        ]]);
    }

    private static function webhook(array $form, array $data, array $meta, int $id): void
    {
        $url = trim((string)($form['SETTINGS']['webhook_url'] ?? ''));
        if ($url === '' || !preg_match('~^https://~i', $url)) { return; }
        $body = json_encode(['event' => 'smartform.submitted', 'form' => ['id' => $form['ID'], 'name' => $form['NAME']], 'submission' => ['id' => $id, 'data' => $data, 'meta' => $meta]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $client = new HttpClient(['socketTimeout' => 3, 'streamTimeout' => 3]);
        $secret = (string)($form['SETTINGS']['webhook_secret'] ?? '');
        $client->setHeader('Content-Type', 'application/json');
        $client->setHeader('X-Fertem-Signature', hash_hmac('sha256', $body, $secret));
        $client->post($url, $body);
    }
}

