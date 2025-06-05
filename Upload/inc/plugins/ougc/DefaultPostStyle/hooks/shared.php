<?php

/***************************************************************************
 *
 *    OUGC Default Post Style plugin (/inc/plugins/ougc/DefaultPostStyle/shared.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012-2014 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allow users to set a default style for their posts.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

namespace ougc\DefaultPostStyle\Hooks\Shared;

use MyBB;

use PostDataHandler;

use function ougc\DefaultPostStyle\Core\getSetting;
use function ougc\DefaultPostStyle\Core\getUserTemplate;
use function ougc\DefaultPostStyle\Core\isIgnoredForum;

function datahandler_post_validate_thread(PostDataHandler $postDataHandler): PostDataHandler
{
    return datahandler_post_validate_post($postDataHandler);
}

function datahandler_post_validate_post(PostDataHandler $postDataHandler): PostDataHandler
{
    global $mybb;
    global $ougcDefaultPostStyleSelectedTemplateID;

    $forumID = (int)$postDataHandler->data['fid'];

    if (isIgnoredForum($forumID) || !is_member(
            getSetting('groups'),
            get_user($postDataHandler->data['uid'])
        )) {
        return $postDataHandler;
    }

    if (!isset($mybb->input['ougcDefaultPostStyleTemplateID'])) {
        return $postDataHandler;
    }

    $selectedTemplateID = $ougcDefaultPostStyleSelectedTemplateID = $mybb->get_input(
        'ougcDefaultPostStyleTemplateID',
        MyBB::INPUT_INT
    );

    $userID = (int)$postDataHandler->data['uid'];

    $templateData = getUserTemplate($selectedTemplateID, $userID);

    if (!$userID || empty($templateData['isEnabled'])) {
        return $postDataHandler;
    }

    $ougcDefaultPostStyleSelectedTemplateID = (int)$templateData['templateID'];

    return $postDataHandler;
}

function datahandler_post_insert_thread_post(PostDataHandler $postDataHandler): PostDataHandler
{
    return datahandler_post_insert_post($postDataHandler);
}

function datahandler_post_insert_post(PostDataHandler $postDataHandler): PostDataHandler
{
    global $ougcDefaultPostStyleSelectedTemplateID;

    if (isset($ougcDefaultPostStyleSelectedTemplateID)) {
        if (isset($postDataHandler->post_update_data)) {
            $postDataHandler->post_update_data['ougcDefaultPostStyleTemplateID'] = (int)$ougcDefaultPostStyleSelectedTemplateID;
        }

        if (isset($postDataHandler->post_insert_data)) {
            $postDataHandler->post_insert_data['ougcDefaultPostStyleTemplateID'] = (int)$ougcDefaultPostStyleSelectedTemplateID;
        }
    }

    return $postDataHandler;
}

function datahandler_post_update(PostDataHandler $postDataHandler): PostDataHandler
{
    return datahandler_post_insert_post($postDataHandler);
}