<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_googledrive;
defined('MOODLE_INTERNAL') || die;

//require_once($CFG->libdir . '/google/lib.php');
require_once($CFG->dirroot.'/mod/googledrive/vendor/autoload.php');
//require_once('../vendor/autoload.php');

// To be able to use our custom IO class.
//require_once($CFG->libdir . '/google/curlio.php');

/**
 * Google Docs Plugin
 *
 * @since Moodle 3.1
 * @package    mod_googledrive
 * @copyright  2016 Nadav Kavalerchik <nadavkav@gmail.com>
 * @copyright  2009 Dan Poltawski <talktodan@gmail.com> (original work)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class googledrive {

    /**
     * Google Client.
     * @var Google_Client
     */
    private $client = null;

    /**
     * Google Drive Service.
     * @var Google_Drive_Service
     */
    private $service = null;

    /**
     * Session key to store the accesstoken.
     * @var string
     */
    const SESSIONKEY = 'googledrive_rwaccesstoken';

    /**
     * URI to the callback file for OAuth.
     * @var string
     */
    const CALLBACKURL = '/admin/oauth2callback.php';

    // calling mod_url cmid
    private $cmid = null;

    // Author (probably the teacher) array(type, role, emailAddress, displayName)
    private $author = array();

    // List (array) of students (array)
    private $students = array();

    /**
     * Constructor.
     *
     * @param int $cmid mod_googledrive instance id.
     * @return void
     */
    public function __construct($cmid) {
        global $CFG;

        $this->cmid = $cmid;
        $callbackurl = new \moodle_url(self::CALLBACKURL);

        //make_temp_directory('googleapi');
        //$tempdir = $CFG->tempdir . '/googleapi';

        // Moodle's old google client API v2 ?
        //$this->client = get_google_client();

        // Google new API v3 (included in this plugin via composer)
        // available inside vedor folder.
        $this->client = new \Google_Client();

        //$this->client->setApplicationName('Moodle ' . $CFG->release);
        //$this->client->setIoClass('moodle_google_curlio');

        // Google "web" client (used with regular Moodle old Google APIs)
        $this->client->setClientId(get_config('googledocs', 'clientid'));
        $this->client->setClientSecret(get_config('googledocs', 'secret'));

        $this->client->setScopes(array(
            \Google_Service_Drive::DRIVE,
            \Google_Service_Drive::DRIVE_APPDATA,
            \Google_Service_Drive::DRIVE_METADATA,
            \Google_Service_Drive::DRIVE_FILE));
        $this->client->setRedirectUri($callbackurl->out(false));
        //$this->client->setAccessType('online');
        //$this->client->setClassConfig('Google_Cache_File', 'directory', $tempdir);
        //$this->client->setClassConfig('Google_Auth_OAuth2', 'access_type', 'online');
        //$this->client->setClassConfig('Google_Auth_OAuth2', 'approval_prompt', 'auto');
        $this->service = new \Google_Service_Drive($this->client);

        //if (!$this->check_google_login()) {
        //    $this->display_login_button();
        //}
    }

    /**
     * Returns the access token if any.
     *
     * @return string|null access token.
     */
    protected function get_access_token() {
        global $SESSION;
        if (isset($SESSION->{self::SESSIONKEY})) {
            return $SESSION->{self::SESSIONKEY};
        }
        return null;
    }

    /**
     * Store the access token in the session.
     *
     * @param string $token token to store.
     * @return void
     */
    protected function store_access_token($token) {
        global $SESSION;
        $SESSION->{self::SESSIONKEY} = $token;
    }

    /**
     * Callback method during authentication.
     *
     * @return void
     */
    public function callback() {
        if ($code = optional_param('oauth2code', null, PARAM_RAW)) {
            $this->client->authenticate($code);
            $this->store_access_token($this->client->getAccessToken());
        }
    }

    /**
     * Checks whether the user is authenticate or not.
     *
     * @return bool true when logged in.
     */
    public function check_google_login() {
        if ($token = $this->get_access_token()) {
            $this->client->setAccessToken($token);
            return true;
        }
        return false;
    }

    /**
     * Return HTML link to Google authentication service.
     *
     * @return string HTML link to Google authentication service.
     */
    public function display_login_button() {
        // Following link is calling above callback() function on successful authentication.
        $returnurl = new \moodle_url('/mod/googledrive/gdrive_oauth2_callback.php');
        $returnurl->param('callback', 'yes');
        $returnurl->param('cmid', $this->cmid);
        $returnurl->param('sesskey', sesskey());

        $url = new \moodle_url($this->client->createAuthUrl());
        $url->param('state', $returnurl->out_as_local_url(false));
        return '<a role="button" onClick="window.open(\''.$url->out(false).'\', \'_new\', \'height=400,width=600\');"><span class="btn">'.
            get_string('login', 'repository').'</span></a>';
        //return '<a class="btn" target="_blank" href="'.$url->out(false).'">'.get_string('login', 'repository').'</a>';
    }


    public function read_gdrive_files() {
        // Get the API client and construct the service object.
        //$client = getClient();
        //$service = new Google_Service_Drive($client);

        // Print the names and IDs for up to 10 files.
        $optParams = array(
            'pageSize' => 10,
            'fields' => 'nextPageToken, files(id, name)'
        );
        $results = $this->service->files->listFiles($optParams);

        if (count($results->getFiles()) == 0) {
            print "No files found.\n";
        } else {
            print "Files:\n";
            foreach ($results->getFiles() as $file) {
                printf("%s (%s)\n", $file->getName(), $file->getId());
            }
        }
    }

    /**
     * Set author details.
     *
     * @param array $author Author details.
     * @return void
     */
    public function set_author($author = array()) {
        $this->author = $author;
    }

    /**
     * Set student's details.
     *
     * @param array $students student's details.
     * @return void
     */
    public function set_students($students = array()) {
        foreach($students as $student) {
            $this->students[] = $student;
        }
        // Or...
        //$this->students = $students;
    }

    /**
     * Create a new Google drive file and share it with proper permissions.
     *
     * @param string $docname Google document name.
     * @param int $gfiletype google drive file type. (default: doc. can be doc/presentation/spreadsheet...)
     * @param int $permissiontype permission type. (default: 0 = teacher can write + students can comment)
     * @param array $author Author details.
     * @param array $students student's details.
     * @return string link to Google drive shared file, if successful or null.
     */
    public function create_gdrive_file($docname, $gfiletype = GDRIVEFILETYPE_DOCUMENT,
                                      $permissiontype = GDRIVEFILEPERMISSION_AUTHER_STUDENTS_RC,
                                      $author = array(), $students = array()) {

        if (!empty($author)) {
            $this->author = $author;
        }

        if (!empty($students)) {
            $this->students = $students;
        }

        $mimetypes = gdrive_filetypes();

        // Create a Google Doc file.
        $fileMetadata = new \Google_Service_Drive_DriveFile(array(
            'name' => $docname,
            // http://stackoverflow.com/questions/11412497/what-are-the-google-apps-mime-types-in-google-docs-and-google-drive#11415443
            'mimeType' => $mimetypes[$gfiletype]['mimetype']));
        $file = $this->service->files->create($fileMetadata, array('fields' => 'id'));
        //printf("File ID: %s\n", $file->id);

        // Set proper permissions on above created file.
        $fileId = $file->id;
        $this->service->getClient()->setUseBatch(true);
        try {
            $batch = $this->service->createBatch();

            // Give proper permissions to author (teacher).
            if (!empty($this->author)) {
                $this->author['type'] = 'user';
                $this->author['role'] = 'writer';
                $userAuthorPermission = new \Google_Service_Drive_Permission($this->author);
            }
            $request1 = $this->service->permissions->create(
                $fileId, $userAuthorPermission, array('fields' => 'id'));
            $batch->add($request1, 'user_author');

            // Give proper permissions to all students.
            $studentpremissions = 'commenter';
            if ($permissiontype == GDRIVEFILEPERMISSION_AUTHER_STUDENTS_RW) {
                $studentpremissions = 'writer';
            }

            if (!empty($this->students)) {
                foreach ($this->students as $student) {
                    $id = $student['id'];
                    $userStudentPermission[$id] = new \Google_Service_Drive_Permission(
                        array(
                            'type' => 'user',
                            'role' => $studentpremissions,
                            'emailAddress' => $student['emailAddress'],
                            //'displayName' => $student['displayName']
                        )
                    );
                    $request[$id] = $this->service->permissions->create(
                        $fileId, $userStudentPermission[$id], array('fields' => 'id'));
                    $batch->add($request[$id], 'user'.$id);
                }

            }
// TODO: consider allowing permission per domain
//    $domainPermission = new Google_Service_Drive_Permission(array(
//        'type' => 'domain',
//        'role' => 'reader',
//        'domain' => 'appsrocks.com'
//    ));
//    $request = $service->permissions->create(
//        $fileId, $domainPermission, array('fields' => 'id'));
//    $batch->add($request, 'domain');

            $results = $batch->execute();

//            foreach ($results as $result) {
//                if ($result instanceof \Google_Service_Exception) {
//                    // Handle error
//                    printf($result);
//                } else {
//                    printf("Permission ID: %s\n", $result->id);
//                }
//            }
        } finally {
            $this->service->getClient()->setUseBatch(false);
        }

        $sharedlink = sprintf($mimetypes[$gfiletype]['linktemplate'], $file->id);
        return $sharedlink;
    }

    /**
     * Logout.
     *
     * @return void
     */
    public function logout() {
        $this->store_access_token(null);
        //return parent::logout();
    }

    /**
     * Get a file.
     *
     * @param string $reference reference of the file.
     * @param string $file name to save the file to.
     * @return string JSON encoded array of information about the file.
     */
    public function get_file($reference, $filename = '') {
        global $CFG;

        $auth = $this->client->getAuth();
        $request = $auth->authenticatedRequest(new Google_Http_Request($reference));
        if ($request->getResponseHttpCode() == 200) {
            $path = $this->prepare_file($filename);
            $content = $request->getResponseBody();
            if (file_put_contents($path, $content) !== false) {
                @chmod($path, $CFG->filepermissions);
                return array(
                    'path' => $path,
                    'url' => $reference
                );
            }
        }
        throw new repository_exception('cannotdownload', 'repository');
    }


    /**
     * Edit/Create Admin Settings Moodle form.
     *
     * @param moodleform $mform Moodle form (passed by reference).
     * @param string $classname repository class name.
     */
    /*
    public static function type_config_form($mform, $classname = 'repository') {

        // TODO: this function is not used, yet.
        // We are using Moodle's google api clientid & secret, for now.

        $callbackurl = new moodle_url(self::CALLBACKURL);

        $a = new stdClass;
        $a->docsurl = get_docs_url('Google_OAuth_2.0_setup');
        $a->callbackurl = $callbackurl->out(false);

        $mform->addElement('static', null, '', get_string('oauthinfo', 'repository_googledocs', $a));

        parent::type_config_form($mform);
        $mform->addElement('text', 'clientid', get_string('clientid', 'repository_googledocs'));
        $mform->setType('clientid', PARAM_RAW_TRIMMED);
        $mform->addElement('text', 'secret', get_string('secret', 'repository_googledocs'));
        $mform->setType('secret', PARAM_RAW_TRIMMED);

        $strrequired = get_string('required');
        $mform->addRule('clientid', $strrequired, 'required', null, 'client');
        $mform->addRule('secret', $strrequired, 'required', null, 'client');
    }
    */
}