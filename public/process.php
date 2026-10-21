<?php
require __DIR__ . '/../src/app.php';

const ANSWER_GRACE_SECONDS = 3;   // an answer sent as the timer runs out still counts

require_post();

switch ($_POST['action'] ?? '') {
    case 'start':
        $user = require_login();
        $_SESSION['test'] = ['result_id' => create_result($user['id']), 'deadlines' => []];
        redirect('indextest.php?section=' . urlencode(array_key_first(SECTIONS)));

    case 'answer':
        $user = require_login();
        $test = require_test();
        $name = $_POST['section'] ?? '';
        $section = is_string($name) ? section($name) : null;
        $number = filter_var($_POST['number'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($section === null || $number === false || $number > question_count($name)) {
            http_response_code(400);
            exit('Permintaan tidak valid.');
        }

        // the server's clock decides: no answer before the section started or after it ended
        $deadline = $test['deadlines'][$name] ?? null;
        if ($deadline === null || time() > $deadline + ANSWER_GRACE_SECONDS) {
            redirect(after_section($name));
        }

        $given = [];
        foreach (answer_fields($section) as $field) {
            $value = $_POST[$field] ?? '';
            $given[] = is_string($value) ? mb_substr($value, 0, MAX_ANSWER_LENGTH) : '';
        }
        $marks = mark_answer($section, correct_answer($name, $number) ?? '', $given);
        $position = ($number - 1) * slots_per_question($section);
        if ($position + strlen($marks) > $section['length']) {
            throw new LogicException("section_$name has more questions than result_$name has slots");
        }
        write_marks($test['result_id'], $user['id'], $name, $position, $marks);

        redirect($number === question_count($name)
            ? after_section($name)
            : 'question.php?section=' . urlencode($name) . '&n=' . ($number + 1));

    default:
        http_response_code(400);
        exit('Permintaan tidak valid.');
}
