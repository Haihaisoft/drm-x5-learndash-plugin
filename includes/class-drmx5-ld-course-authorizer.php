<?php

defined('ABSPATH') || exit;

/** Resolves YourProductID to LearnDash courses and verifies access. */
class DRMX5_LD_Course_Authorizer {
    /** Return the first listed LearnDash course the user may access. */
    public static function get_accessible_course($product_ids, $user_id) {
        if (!function_exists('sfwd_lms_has_access')) {
            return false;
        }

        $tokens = preg_split('/\s*-\s*/', trim((string) $product_ids), -1, PREG_SPLIT_NO_EMPTY);
        foreach (array_unique($tokens) as $token) {
            if (!ctype_digit($token) || (int) $token <= 0) {
                continue;
            }
            $course_id = (int) $token;
            if (get_post_type($course_id) !== 'sfwd-courses' || !sfwd_lms_has_access($course_id, (int) $user_id)) {
                continue;
            }
            if (function_exists('ld_course_access_expired') && ld_course_access_expired($course_id, (int) $user_id)) {
                continue;
            }

            $start = function_exists('ld_course_access_from')
                ? (int) ld_course_access_from($course_id, (int) $user_id) : 0;
            $end = function_exists('ld_course_access_expires_on')
                ? (int) ld_course_access_expires_on($course_id, (int) $user_id) : 0;
            if ($end && $end < time()) {
                continue;
            }
            if (!$start) {
                $start = time();
            }

            return array(
                'id' => $course_id,
                'start' => $start,
                'end' => $end ?: strtotime('+10 years'),
            );
        }
        return false;
    }
}

