<?php
/**
 * @version $Id: Rideboard.php 7776 2010-06-11 13:52:58Z jtickle $
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

PHPWS_Core::requireInc('rideboard', 'defines.php');

class Rideboard {
    public $ride     = null;
    public $panel    = null;
    public $content  = null;
    public $title    = null;
    public $message  = array();
    public $carpool  = null;

    public function admin()
    {
        if (!Current_User::allow('rideboard')) {
            Current_User::disallow();
        }

        $js = false;

        $this->loadAdminPanel();

        $command = @ $_REQUEST['aop'];
        if (empty($command) || $command == 'main') {
            $command = $this->panel->getCurrentTab();
        }

        switch ($command) {
            case 'locations':
                $this->locations();
                break;

            case 'settings':
                $this->settings();
                break;

            case 'edit_location':
                $js = true;
                $this->editLocation();
                break;

            case 'post_location':
                if (!Current_User::authorized('rideboard')) {
                    Current_User::disallow(null, false);
                }

                $this->postLocation();
                if (isset($_POST['lid'])) {
                    javascript('close_refresh');
                    $js = true;
                } else {
                    PHPWS_Core::goBack();
                }
                break;

            case 'purge_rides':
                if (!Current_User::authorized('rideboard')) {
                    Current_User::disallow(null, false);
                }
                $this->purgeRides();
                PHPWS_Core::reroute(PHPWS_Text::linkAddress('rideboard', array('aop'=>'settings')));
                break;


            case 'post_settings':
                if (!Current_User::authorized('rideboard')) {
                    Current_User::disallow(null, false);
                }
                PHPWS_Settings::set('rideboard', 'default_slocation', (int)$_POST['default_slocation']);
                PHPWS_Settings::set('rideboard', 'miles_or_kilometers', (int)$_POST['miles_or_kilometers']);
                PHPWS_Settings::set('rideboard', 'carpool', (int)isset($_POST['carpool']));

                $dest = preg_replace('/[^\w,\-\.\:\s]/', '', strip_tags($_POST['default_destination']));

                if (!empty($dest)) {
                    PHPWS_Settings::set('rideboard', 'default_destination', $dest);
                } else {
                    PHPWS_Settings::set('rideboard', 'default_destination', null);
                }
                PHPWS_Settings::save('rideboard');
                $this->settings();
                break;

            case 'add_link':
                if (PHPWS_Core::moduleExists('menu')) {
                    if (PHPWS_Core::initModClass('menu', 'Menu.php')) {
                        Menu::quickLink(dgettext('rideboard', 'Rideboard'), 'index.php?module=rideboard');
                    }
                }
                PHPWS_Core::goBack();
                break;
        }

        $tpl['CONTENT'] = & $this->content;
        $tpl['TITLE']   = & $this->title;
        $tpl['MESSAGE'] = $this->getMessage();


        if ($js) {
            $content = PHPWS_Template::process($tpl, 'rideboard', 'main.tpl');
            Layout::nakedDisplay($content);
        } else {
            $content = PHPWS_Template::process($tpl, 'rideboard', 'panel_main.tpl');
            $this->panel->setContent($content);
            Layout::add(PHPWS_ControlPanel::display($this->panel->display()));
        }
    }

    public function searchSession()
    {
        $_SESSION['rb_search'] = array();

        if (!empty($_POST['search_words'])) {
            $search = preg_replace('/[^\w\s]/', '', $_POST['search_words']);
            $search = preg_replace('/\s{2,}/', ' ', $search);
            $_SESSION['rb_search']['search'] = preg_replace('/\s/', '|', $search);
        }

        $_SESSION['rb_search']['use_date']           = isset($_POST['use_date']);
        $_SESSION['rb_search']['search_time']        = PHPWS_Form::getPostedDate('search_time');
        $_SESSION['rb_search']['search_ride_type']   = (int)$_POST['search_ride_type'];
        $_SESSION['rb_search']['search_gender_pref'] = (int)$_POST['search_gender_pref'];
        $_SESSION['rb_search']['search_smoking']     = (int)$_POST['search_smoking'];
        $_SESSION['rb_search']['search_s_location']  = (int)$_POST['search_s_location'];
        $_SESSION['rb_search']['search_d_location']  = (int)$_POST['search_d_location'];
    }

    public function user()
    {
        Current_User::requireLogin();
        $command = NULL;
        if(isset($_REQUEST['uop']) && $_REQUEST['uop']!="")
        {
            $command = $_REQUEST['uop'];
        }
//        $command = $_REQUEST['uop'];

        $js = false;

        switch ($command) {
            case 'view_ride':
                $js = true;
                $this->viewRide();
                break;

            case 'carpool_form':
                $js = true;
                $this->loadCarpool();
                $this->carpoolForm();
                break;

            case 'carpool':
                $this->carpool();
                break;

            case 'post_carpool':
                $js = true;
                $this->loadCarpool();
                if ($this->postCarpool()) {
                    javascript('close_refresh');
                } else {
                    $this->carpoolForm();
                }
                break;

            case 'search_rides':
                $this->searchRides();
                break;
            case 'user_post':
                if (isset($_POST['post_ride'])) {
                    $this->loadRide();
                    if ($this->postLimit()) {
                        $this->title = dgettext('rideboard', 'Sorry');
                        $this->content = sprintf(dgettext('rideboard', 'You are limited to %s ride posts per account.'),
                        PHPWS_Settings::get('rideboard', 'post_limit'));
                    } elseif ($this->postRide()) {
                        if (PHPWS_Error::logIfError($this->ride->save())) {
                            $this->title = dgettext('rideboard', 'Sorry');
                            $this->content = dgettext('rideboard', 'An error occurred when trying to save your ride. Please try again later.');
                            $this->content .= '<br />' . PHPWS_Text::moduleLink(dgettext('rideboard', 'Return to Ride Board menu.'), 'rideboard');
                        } else {
                            $this->title = dgettext('rideboard', 'Ride posted!');
                            $this->content = PHPWS_Text::moduleLink(dgettext('rideboard', 'Return to Ride Board menu.'), 'rideboard');
                        }
                    } else {
                        $this->userMain();
                    }
                } else {
                    $this->searchSession();
                    PHPWS_Core::reroute(PHPWS_Text::linkAddress('rideboard', array('uop'=>'search_rides')));
                }
                break;

            case 'view_my_rides':
                $this->viewMyRides();
                break;

            case 'delete_ride':
                $this->loadRide();
                if ($this->ride->id) {
                    if ( Current_User::authorized('rideboard') ||
                    (Current_User::verifyAuthKey() && Current_User::getId() == $this->ride->user_id) ) {
                        $this->ride->delete();
                    }
                }
                PHPWS_Core::goBack();
                break;

            case 'delete_carpool':
                $this->loadCarpool();
                if (Current_User::verifyAuthKey() && $this->carpool->allowDelete()) {
                    PHPWS_Error::logIfError($this->carpool->delete());
                }
                PHPWS_Core::goBack();
                break;

            case 'cpinfo':
                $js = true;
                $this->loadCarpool();
                $this->title = dgettext('rideboard', 'Carpool info');
                $this->content = $this->carpool->view();
                break;

            default:
                $this->loadRide();
                $this->userMain();
                break;

        }

        $tpl['CONTENT'] = & $this->content;
        $tpl['TITLE']   = & $this->title;
        $tpl['MESSAGE'] = $this->getMessage();

        $content = PHPWS_Template::process($tpl, 'rideboard', 'main.tpl');
        if ($js) {
            Layout::nakedDisplay($content);
        } else {
            Layout::add($content);
        }
    }


    public function loadAdminPanel()
    {
        $link = PHPWS_Text::linkAddress('rideboard', array('aop'=>'main'));;
        $tabs['locations']      = array ('title' => dgettext('rideboard', 'Locations'),
                                         'link'  => $link);

        $tabs['settings']      = array ('title' => dgettext('rideboard', 'Settings'),
                                        'link'  => $link);

        $this->panel = new PHPWS_Panel('rideboard-admin');
        $this->panel->quickSetTabs($tabs);
    }

    public function getMessage()
    {
        return implode('<br />', $this->message);
    }

    public function locationForm($id=0)
    {
        $form = new PHPWS_Form('location');
        $form->addHidden('module', 'rideboard');
        $form->addHidden('aop', 'post_location');
        $form->addText('city_state');
        if ($id) {
            $db = new PHPWS_DB('rb_location');
            $db->addWhere('id', (int)$id);
            $db->addColumn('city_state');
            $location = $db->select('one');
            if (!PHPWS_Error::logIfError($location) && !empty($location)) {
                $form->addHidden('lid', $id);
                $form->setValue('city_state', $location);
            }
            // edit uses the javascript popup
            $form->setLabel('city_state', dgettext('rideboard', 'Edit location'));
            $form->addTplTag('CANCEL', javascript('close_window'));
        } else {
            $form->setLabel('city_state', dgettext('rideboard', 'New location'));
            $form->setClass('city_state', dgettext('rideboard', 'form-control'));
            
        }
        $form->addSubmit(dgettext('rideboard', 'Go'));
        $form->setClass('submit', 'btn btn-primary');
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'rideboard', 'edit_location.tpl');
    }

    public function locations()
    {
        $this->title = dgettext('rideboard', 'Edit locations');
        PHPWS_Core::initCoreClass('DBPager.php');
        $tpl['ADD_LOCATION'] = $this->locationForm();
        $tpl['LOCATION_LABEL'] = dgettext('rideboard', 'Locations');

        $pager = new DBPager('rb_location');
        $pager->setModule('rideboard');
        $pager->setTemplate('location.tpl');
        $pager->addPageTags($tpl);
        $pager->addToggle('class="bgcolor1"');
        $pager->addRowFunction(array('Rideboard', 'locationRow'));
        $pager->setDefaultOrder('city_state');

        $this->content = $pager->get();
    }

    public function editLocation()
    {
        $this->title = dgettext('rideboard', 'Edit location');
        $this->content = $this->locationForm($_GET['lid']);
    }

    public function postLocation()
    {
        if(empty($_POST['city_state'])) {
            return;
        }

        $db = new PHPWS_DB('rb_location');
        $db->addValue('city_state', strip_tags($_POST['city_state']));
        if (isset($_POST['lid'])) {
            $db->addWhere('id', (int)$_POST['lid']);
            PHPWS_Error::logIfError($db->update());
        } else {
            PHPWS_Error::logIfError($db->insert());
        }
    }

    public function getLocations()
    {
        $db = new PHPWS_DB('rb_location');
        $db->addColumn('id');
        $db->addColumn('city_state');
        $db->addOrder('city_state');

        $db->setIndexBy('id');
        return $db->select('col');
    }

    public static function locationRow($value)
    {
        $js['address'] = PHPWS_Text::linkAddress('rideboard', array('aop'=>'edit_location',
                                                                    'lid'=>$value['id']),
        true);
        $js['label'] = dgettext('rideboard', 'Edit');
        $js['link_title'] = sprintf(dgettext('rideboard', 'Edit the location %s'), $value['city_state']);
        $js['height'] = 180;
        $links[] = javascript('open_window', $js);
        $tpl['LINKS'] = implode(' | ', $links);
        return $tpl;
    }

    public function settings()
    {
        $form = new PHPWS_Form('settings');
        $form->addHidden('module', 'rideboard');
        $form->addHidden('aop', 'post_settings');

        $locations = $this->getLocations();

        if (PHPWS_Error::logIfError($locations) || empty($locations)) {
            $locations = array(0=> dgettext('rideboard', 'No default'));
        }

        $form->addSelect('default_slocation', $locations);
        $form->setLabel('default_slocation', dgettext('rideboard', 'Default starting location'));
        $form->setClass('default_slocation', 'form-control');
        $form->setMatch('default_slocation', PHPWS_Settings::get('rideboard', 'default_slocation'));
        $form->addSubmit(dgettext('rideboard', 'Save settings'));


        $form->addRadio('miles_or_kilometers', array(0,1));
        $form->setLabel('miles_or_kilometers', array(0=>dgettext('rideboard', 'Miles'),
        1=>dgettext('rideboard', 'Kilometers')));
        $form->setMatch('miles_or_kilometers', PHPWS_Settings::get('rideboard', 'miles_or_kilometers'));

        $form->addCheck('carpool', 1);
        $form->setMatch('carpool', PHPWS_Settings::get('rideboard', 'carpool'));
        $form->setLabel('carpool', dgettext('rideboard', 'Enable carpooling'));

        $form->addText('default_destination', PHPWS_Settings::get('rideboard', 'default_destination'));
        $form->setLabel('default_destination', dgettext('rideboard', 'Default destination'));
        $form->setClass('default_destination', 'form-control');
        $form->setSize('default_destination', 40);

        $tpl = $form->getTemplate();

        $db = new PHPWS_DB('rb_ride');
        $db->addWhere('depart_time', time(), '<');
        $db->addColumn('id');
        $old_rides = $db->count();

        if ($old_rides) {
            $tpl['PURGE'] = PHPWS_Text::secureLink(sprintf(dngettext('rideboard', 'You have %s ride that has expired. Click here to purge it.',
                                                                     'You have %s rides that have expired. Click here to purge them.', $old_rides),
            $old_rides),
                                                   'rideboard', array('aop'=>'purge_rides')
            );
        } else {
            $tpl['PURGE'] = dgettext('rideboard', 'No rides need purging.');
        }

        $tpl['MENU'] = PHPWS_Text::moduleLink(dgettext('rideboard', 'Add menu link'), 'rideboard', array('aop'=>'add_link'));

        $tpl['DISTANCE_LABEL'] = dgettext('rideboard', 'Distance format');
        $this->content = PHPWS_Template::process($tpl, 'rideboard', 'settings.tpl');
        $this->title = dgettext('rideboard', 'Rideboard Settings');
    }

    public function loadRide()
    {
        PHPWS_Core::initModClass('rideboard', 'Ride.php');

        if (isset($_REQUEST['rid'])) {
            $this->ride = new RB_Ride($_REQUEST['rid']);
        } else {
            $this->ride = new RB_Ride;
        }
    }

    public function userMain()
    {
        $ride = & $this->ride;

        /*
         if ($ride->id) {
         $this->title = dgettext('rideboard', 'Update ride');
         } else {
         $this->title = dgettext('rideboard', 'Post ride');
         }
         */

        $locations = $this->getLocations();
        if (PHPWS_Error::logIfError($locations) || empty($locations)) {
            $locations = array(0 => dgettext('rideboard', '- Location -'));
        } else {
            $locations = array_reverse($locations, true);
            $locations[0] = dgettext('rideboard', '- Locations -');
            $locations = array_reverse($locations, true);
        }


        $form = new PHPWS_Form('ride');
        $form->addHidden('module', 'rideboard');
        $form->addHidden('uop', 'user_post');

        /*
         * Post ride form
         */
        $this->postForm($form, $locations);

        /*
         * Search ride form
         */
        $this->searchForm($form, $locations);

        $tpl = $form->getTemplate();
        $tpl['DEPART_TIME_LABEL'] = dgettext('rideboard', 'Leaving on');

        $js_vars['form_name'] = 'ride';
        $js_vars['date_name'] = 'depart_time';
        $js_vars['type']      = 'select';
        $tpl['DEPART_JS'] = javascript('js_calendar', $js_vars);

        //$js_vars['date_name'] = 'search_time';
        //$tpl['SEARCH_JS'] = javascript('js_calendar', $js_vars);

        $tpl['POST_TITLE'] = dgettext('rideboard', 'Post ride');
        $tpl['SEARCH_TITLE'] = dgettext('rideboard', 'Search for ride');

        $links[] = PHPWS_Text::secureBootstrapButton(dgettext('rideboard', 'View my rides'), 'rideboard',
        array('uop' => 'view_my_rides'));

        if (PHPWS_Settings::get('rideboard', 'carpool')) {
         $links[] = PHPWS_Text::secureBootstrapButton(dgettext('rideboard', 'Carpool'), 'rideboard',
            array('uop' => 'carpool'));
        }


        $tpl['LINKS'] = implode(' | ', $links);
        $this->content = PHPWS_Template::process($tpl, 'rideboard', 'ride_form.tpl');
    }

    public function postForm(&$form, $locations)
    {
        javascript('datetimepicker', null, false, true, true);
        $ride = & $this->ride;
        $form->addSelect('ride_type', array(RB_RIDER  => dgettext('rideboard', 'Looking for a ride'),
        RB_DRIVER => dgettext('rideboard', 'Offering to drive'),
        RB_EITHER => dgettext('rideboard', 'Either')));
        $form->setLabel('ride_type', dgettext('rideboard', 'I am'));
        $form->setMatch('ride_type', $ride->ride_type);
        $form->setClass('ride_type', 'form-control');


        $form->addSelect('gender_pref', array(RB_MALE   => dgettext('rideboard', 'Male'),
        RB_FEMALE => dgettext('rideboard', 'Female'),
        RB_EITHER => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('gender_pref', dgettext('rideboard', 'Gender preference'));
        $form->setMatch('gender_pref', $ride->gender_pref);
        $form->setClass('gender_pref', 'form-control');

        $form->addSelect('smoking', array(RB_NONSMOKER  => dgettext('rideboard', 'Non-smokers only'),
        RB_SMOKER     => dgettext('rideboard', 'Prefer smokers'),
        RB_EITHER     => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('smoking', dgettext('rideboard', 'Smoking preference'));
        $form->setMatch('smoking', $ride->smoking);
        $form->setClass('smoking', 'form-control');

        //$form->dateSelect('depart_time', $ride->depart_time, null, 0, 1);
        $form->addText('depart_time');
        $form->setClass('depart_time', 'form-control datetimepicker');
        
        

        $form->addText('title', $ride->title);
        $form->setLabel('title', dgettext('rideboard', 'Trip title'));
        $form->setClass('title', 'form-control');

        $form->addSelect('s_location', $locations);
        $form->setLabel('s_location', dgettext('rideboard', 'Leaving from'));
        $form->setMatch('s_location', $ride->s_location);
        $form->setClass('s_location', 'form-control');

        $form->addSelect('d_location', $locations);
        $form->setLabel('d_location', dgettext('rideboard', 'Destination'));
        $form->setMatch('d_location', $ride->d_location);
        $form->setClass('d_location', 'form-control');
        
        $form->addTextArea('comments', $ride->comments);
        $form->setLabel('comments', dgettext('rideboard', 'Comments'));
        $form->setClass('comments', 'form-control');
        $form->addSubmit('post_ride', dgettext('rideboard', 'Post ride'));
        $form->setClass('post_ride', 'btn btn-default');
    }

    public function searchForm($form, $locations)
    {
     javascript('datetimepicker', null, false, true, true);
     $locations[0] = dgettext('rideboard', '- Any -');
        $form->addSelect('search_s_location', $locations);
        $form->setLabel('search_s_location', dgettext('rideboard', 'Leaving from'));
        $form->setClass('search_s_location', 'form-control');

        $form->addSelect('search_d_location', $locations);
        $form->setLabel('search_d_location', dgettext('rideboard', 'Destination'));
        $form->setClass('search_d_location', 'form-control');

        /* $form->dateSelect('search_time', null, null, 0, 1);
        $form->setClass('search_time', 'form-control'); */
        
        $form->addText('search_time');
        $form->setClass('search_time', 'form-control datetimepicker');
        
        
        
        $form->addSubmit('search_ride', dgettext('rideboard', 'Search for rides'));
        $form->setClass('search_ride', 'btn btn-default');
        
       
        
        $form->addText('search_words');
        $form->setLabel('search_words', dgettext('rideboard', 'Search words'));
        $form->setClass('search_words', 'form-control');

        $form->addSelect('search_ride_type', array(RB_RIDER  => dgettext('rideboard', 'Rider'),
        RB_DRIVER => dgettext('rideboard', 'Drivers'),
        RB_EITHER => dgettext('rideboard', 'Anyone')));
        $form->setMatch('search_ride_type', RB_EITHER);
        $form->setLabel('search_ride_type', dgettext('rideboard', 'Looking for'));
        $form->setClass('search_ride_type', 'form-control');

        $form->addSelect('search_gender_pref', array(RB_MALE   => dgettext('rideboard', 'Male'),
        RB_FEMALE => dgettext('rideboard', 'Female'),
        RB_EITHER => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('search_gender_pref', dgettext('rideboard', 'Gender preference'));
        $form->setMatch('search_gender_pref', RB_EITHER);
        $form->setClass('search_gender_pref', 'form-control');

        $form->addSelect('search_smoking', array(RB_NONSMOKER  => dgettext('rideboard', 'Non-smokers only'),
        RB_SMOKER     => dgettext('rideboard', 'Prefer smokers'),
        RB_EITHER     => dgettext('rideboard', 'Does not matter')));
        $form->setLabel('search_smoking', dgettext('rideboard', 'Smoking preference'));
        $form->setMatch('search_smoking', RB_EITHER);
        $form->setClass('search_smoking', 'form-control');

        $form->addCheck('use_date', 1);
        $form->setLabel('use_date', dgettext('rideboard', 'Leaving around'));
        $form->addTplTag('NOTE',  dgettext('rideboard', 'uncheck to disregard'));
    }

    public function postRide()
    {
        if (PHPWS_Core::isPosted()) {
            return false;
        }

        $errors = array();

        if (empty($_POST['title'])) {
            $errors[] = dgettext('rideboard', 'Please give your ride a trip title.');
        } else {
            $this->ride->setTitle($_POST['title']);
        }

        $this->ride->s_location = (int)$_POST['s_location'];
        $this->ride->d_location = (int)$_POST['d_location'];

        if ($this->ride->s_location &&
        $this->ride->s_location == $this->ride->d_location) {
            $errors[] = dgettext('rideboard', 'Your leaving and arriving locations must be different.');
        }

        if (PHPWS_Form::testDate('depart_time')) {
            $this->ride->depart_time = (int)PHPWS_Form::getPostedDate('depart_time');
            if ($this->ride->depart_time < time()) {
                $errors[] = dgettext('rideboard', 'Your leaving date must be in the future.');
            }
        } else {
            $errors[] = dgettext('rideboard', 'Invalid leaving date');
        }

        if (empty($_POST['comments']) && (!$this->ride->s_location || !$this->ride->d_location)) {
            $errors[] = dgettext('rideboard', 'If you haven\'t set your leaving and arriving locations, you must add information to your comments.');
        } else {
            $this->ride->setComments($_POST['comments']);
        }

        $this->ride->ride_type   = (int)$_POST['ride_type'];
        $this->ride->gender_pref = (int)$_POST['gender_pref'];
        $this->ride->smoking     = (int)$_POST['smoking'];

        if (!empty($errors)) {
            $this->message = $errors;
            return false;
        } else {
            return true;
        }
    }

    public function postLimit()
    {
        $db = new PHPWS_DB('rb_ride');
        $db->addWhere('user_id', Current_User::getId());
        $result = $db->count();
        if (PHPWS_Error::logIfError($result)) {
            return true;
        }

        return ($result >= PHPWS_Settings::get('rideboard', 'post_limit'));
    }

    public function getRidesDB()
    {
        $db = new PHPWS_DB('rb_ride');
        $db->addTable('rb_location', 't1');
        $db->addTable('rb_location', 't2');
        $db->loadClass('rideboard', 'Ride.php');
        $db->addOrder('depart_time desc');
        $db->addColumn('*');
        $db->addJoin('left', 'rb_ride', 't1', 's_location', 'id');
        $db->addJoin('left', 'rb_ride', 't2', 'd_location', 'id');

        $db->addColumn('t1.city_state', null, 'start_location');
        $db->addColumn('t2.city_state', null, 'dest_location');

        return $db;
    }

    public function viewMyRides()
    {
        $this->title = dgettext('rideboard', 'View my Rides');
        $db = $this->getRidesDB();
        $db->addWhere('user_id', Current_User::getId());

        $result = $db->getObjects('RB_Ride');

        if (PHPWS_Error::logIfError($result)) {
            $this->content = dgettext('rideboard', 'An error occurred when pulling your rides.');
            return;
        }

        if (empty($result)) {
            $this->content = dgettext('rideboard', 'You do not have any rides.');
            return;
        }

        foreach ($result as $ride) {
            $tpl['rides'][] = $ride->tags();
        }

        $tpl['TITLE_LABEL']     = dgettext('rideboard', 'Trip title');
        $tpl['RIDE_TYPE_LABEL'] = dgettext('rideboard', 'Driver or Rider');
        $tpl['PREF_LABEL']      = dgettext('rideboard', 'Gender - Smoking Preference');
        $tpl['START_LABEL']     = dgettext('rideboard', 'Leaving from');
        $tpl['DEST_LABEL']      = dgettext('rideboard', 'Destination');

        $this->content = PHPWS_Template::process($tpl, 'rideboard', 'my_rides.tpl');
    }

    public function searchRides()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('rideboard', 'Ride.php');

        if (!isset($_SESSION['rb_search'])) {
            $this->title = dgettext('rideboard', 'Sorry');
            $this->content = dgettext('rideboard', 'Your session timed out.');
            $this->content .= '<br />' . PHPWS_Text::moduleLink(dgettext('rideboard', 'Back to search'), 'rideboard');
            return;
        }

        $tpl['LINK'] = PHPWS_Text::moduleLink(dgettext('rideboard', 'Back to search'), 'rideboard');
        $tpl['TITLE_LABEL']     = dgettext('rideboard', 'Trip title');
        $tpl['RIDE_TYPE_LABEL'] = dgettext('rideboard', 'Driver or Rider');
        $tpl['RIDE_TYPE_ABBR']  = dgettext('rideboard', 'D/R');
        $tpl['GEN_PREF_LABEL']  = dgettext('rideboard', 'Gender');
        $tpl['SMOKE_LABEL']     = dgettext('rideboard', 'Smoking');
        $tpl['START_LABEL']     = dgettext('rideboard', 'Leaving from/on');
        $tpl['DEST_LABEL']      = dgettext('rideboard', 'Destination');

        $pager = new DBPager('rb_ride', 'RB_Ride');
        $pager->setModule('rideboard');
        $pager->setTemplate('search_rides.tpl');
        $pager->addRowTags('tags', false);
        $pager->addPageTags($tpl);
        $pager->setEmptyMessage(dgettext('rideboard', '<div class="alert alert-danger">No rides found fitting your criteria.</div>'));

        $pager->joinResult('s_location', 'rb_location', 'id', 'city_state', 'start_location');
        $pager->joinResult('d_location', 'rb_location', 'id', 'city_state', 'dest_location');

        extract($_SESSION['rb_search']);

        if (!empty($search)) {
            $pager->db->addWhere('title', $search, 'regexp', 'or', 1);
            $pager->db->addWhere('comments', $search, 'regexp', 'or', 1);
        }

        if ($search_s_location) {
            $pager->db->addWhere('s_location', $search_s_location);
        }

        if ($search_d_location) {
            $pager->db->addWhere('d_location', $search_d_location);
        }


        if ($use_date) {
            $search_before = $search_time - (86400 * 7);
            $search_after  = $search_time + (86400 * 7);

            if ($search_before < time()) {
                $search_before = time();
            }

            $pager->db->addWhere('depart_time', $search_before, '>', null, 'time');
            $pager->db->addWhere('depart_time', $search_after, '<', null, 'time');
        }

        if ($search_ride_type != RB_EITHER) {
            $pager->db->addWhere('ride_type', $search_ride_type);
        }

        if ($search_smoking != RB_EITHER) {
            $pager->db->addWhere('smoking', $search_smoking);
        }

        if ($search_gender_pref != RB_EITHER) {
            $pager->db->addWhere('gender_pref', $search_gender_pref);
        }

        $this->title = dgettext('rideboard', 'Search rides');
        $this->content = $pager->get();
    }

    public function viewRide()
    {
        $this->loadRide();
        if (!$this->ride->id) {
            $this->title = dgettext('rideboard', 'Sorry');
            $this->content = dgettext('rideboard', 'This ride could not be found.');
            return;
        }
        $this->title = $this->ride->title;
        $this->content = $this->ride->view();
    }

    public function purgeRides()
    {
        $db = new PHPWS_DB('rb_ride');
        $db->addWhere('depart_time', time(), '<');
        return !PHPWS_Error::logIfError($db->delete());
    }

    public function carpool()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('rideboard', 'Carpool.php');
        
       

        $tpl['LINK'] = javascript('open_window',
        array('address' => PHPWS_Text::linkAddress('rideboard',
        array('uop'=>'carpool_form')),
                                        'label'=> dgettext('rideboard', 'Create a carpool'),
                                        'width'=>640, 'height'=>580));

        $pager = new DBPager('rb_carpool', 'RB_Carpool');
//        $pager->db->addColumn('rb_location.city_state', NULL, 'location');
        $pager->setModule('rideboard');
        $pager->setTemplate('carpools.tpl');
        $pager->addRowTags('row_tags', false);
        $pager->addPageTags($tpl);        
        $pager->setEmptyMessage(dgettext('rideboard', 'No carpool offers found.'));
        $pager->addSortHeader('created', dgettext('rideboard', 'Date created'));
        $pager->joinResult('dest_address', 'rb_location', 'id', 'city_state', 'dest_address');
        $pager->addSortHeader('start_address', dgettext('rideboard', 'Start'));
        $pager->addSortHeader('dest_address', dgettext('rideboard', 'Destination'));
        $pager->setSearch('start_address');
        $pager->addToggle('toggle1');
        $pager->disableSearchLabel();


        $this->title = dgettext('rideboard', 'Carpools');
        $this->content = $pager->get();
    }

    public function carpoolForm()
    {
        $form = new PHPWS_Form('carpool');
        $locations = $this->getLocations();
        $form->addHidden('module', 'rideboard');
        $form->addHidden('uop', 'post_carpool');
        if ($this->carpool->id) {
            $form->addHidden('cid', $this->carpool->id);
        }

        $form->addText('start_address', $this->carpool->start_address);
        $form->setRequired('start_address');
        $form->setLabel('start_address', dgettext('rideboard', 'Starting area (location near you, NOT your physical address)'));
        $form->setClass('start_address', 'form-control');

        //$form->addText('dest_address', $this->carpool->dest_address);
        $form->addSelect('dest_address', $locations);
        $form->setRequired('dest_address');
        $form->setClass('dest_address', 'form-control');
        $form->setMatch('dest_address', PHPWS_Settings::get('rideboard', 'dest_address'));
        $form->setLabel('dest_address', dgettext('rideboard', 'Destination (Any of Amarillo College\'s campuses or intern/work partners)'));

        $form->addTextArea('comment', $this->carpool->comment);
        $form->setClass('comment', 'form-control');
        $form->setLabel('comment', dgettext('rideboard', 'Comments'));

        $form->addSubmit(dgettext('rideboard', 'Save carpool'));
        $form->setClass('submit', 'btn btn-default');
        
        
        

        $tpl = $form->getTemplate();

        $tpl['DIRECTIONS'] = dgettext('rideboard', '');

        if ($this->carpool->id) {
            $this->title = dgettext('rideboard', 'Update carpool');
        } else {
            $this->title = dgettext('rideboard', 'Create a new carpool');
        }
        $this->content = PHPWS_Template::process($tpl, 'rideboard', 'edit_carpool.tpl');
    }

    public function loadCarpool()
    {
        PHPWS_Core::initModClass('rideboard', 'Carpool.php');
        if (isset($_REQUEST['cid'])) {
            $this->carpool = new RB_Carpool((int)$_REQUEST['cid']);
        } else {
            $this->carpool = new RB_Carpool;
        }
    }

    public function postCarpool($admin=false)
    {
//        if (!$this->id) {
            $this->carpool->user_id = Current_User::getId();
            $this->carpool->email   = Current_User::getEmail();
  //      }
        $this->carpool->setAddress('start', $_POST['start_address']);
        $this->carpool->setAddress('dest', $_POST['dest_address']);
        $this->carpool->setComment($_POST['comment']);
        if (empty($this->carpool->start_address)) {
            $this->message = dgettext('rideboard', 'Starting location needs to be entered.');
            return false;
        }

        if (empty($this->carpool->dest_address)) {
            $this->message = dgettext('rideboard', 'Destination location needs to be entered.');
            return false;
        }

        if (empty($this->carpool->created)) {
            $this->carpool->created = time();
        }

        if (PHPWS_Error::logIfError($this->carpool->save())) {
            $this->message = dgettext('rideboard', 'A problem occurred when trying to save your carpool information. Try again later.');
            return false;
        } else {
            return true;
        }
    }
}
?>