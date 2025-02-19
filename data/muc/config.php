<?php defined('MOODLE_INTERNAL') || die();
 $configuration = array (
  'siteidentifier' => '30421e9029368627ee66947a2df0efce',
  'stores' => 
  array (
    'default_application' => 
    array (
      'name' => 'default_application',
      'plugin' => 'file',
      'configuration' => 
      array (
      ),
      'features' => 30,
      'modes' => 3,
      'default' => true,
      'class' => 'cachestore_file',
      'lock' => 'cachelock_file_default',
    ),
    'default_session' => 
    array (
      'name' => 'default_session',
      'plugin' => 'session',
      'configuration' => 
      array (
      ),
      'features' => 14,
      'modes' => 2,
      'default' => true,
      'class' => 'cachestore_session',
      'lock' => 'cachelock_file_default',
    ),
    'default_request' => 
    array (
      'name' => 'default_request',
      'plugin' => 'static',
      'configuration' => 
      array (
      ),
      'features' => 31,
      'modes' => 4,
      'default' => true,
      'class' => 'cachestore_static',
      'lock' => 'cachelock_file_default',
    ),
  ),
  'modemappings' => 
  array (
    0 => 
    array (
      'mode' => 1,
      'store' => 'default_application',
      'sort' => -1,
    ),
    1 => 
    array (
      'mode' => 2,
      'store' => 'default_session',
      'sort' => -1,
    ),
    2 => 
    array (
      'mode' => 4,
      'store' => 'default_request',
      'sort' => -1,
    ),
  ),
  'definitions' => 
  array (
    'core/string' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 30,
      'canuselocalstore' => true,
      'component' => 'core',
      'area' => 'string',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/langmenu' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'canuselocalstore' => true,
      'component' => 'core',
      'area' => 'langmenu',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/databasemeta' => 
    array (
      'mode' => 1,
      'requireidentifiers' => 
      array (
        0 => 'dbfamily',
      ),
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 15,
      'component' => 'core',
      'area' => 'databasemeta',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/eventinvalidation' => 
    array (
      'mode' => 1,
      'staticacceleration' => true,
      'requiredataguarantee' => true,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'eventinvalidation',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/questiondata' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'requiredataguarantee' => false,
      'datasource' => 'question_finder',
      'datasourcefile' => 'question/engine/bank.php',
      'component' => 'core',
      'area' => 'questiondata',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/htmlpurifier' => 
    array (
      'mode' => 1,
      'canuselocalstore' => true,
      'component' => 'core',
      'area' => 'htmlpurifier',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/config' => 
    array (
      'mode' => 1,
      'staticacceleration' => true,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'config',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/groupdata' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 2,
      'component' => 'core',
      'area' => 'groupdata',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/coursehiddengroups' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'core',
      'area' => 'coursehiddengroups',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/calendar_subscriptions' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'core',
      'area' => 'calendar_subscriptions',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/calendar_categories' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simpledata' => true,
      'invalidationevents' => 
      array (
        0 => 'changesincoursecat',
        1 => 'changesincategoryenrolment',
      ),
      'ttl' => 900,
      'component' => 'core',
      'area' => 'calendar_categories',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/capabilities' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 2,
      'ttl' => 3600,
      'component' => 'core',
      'area' => 'capabilities',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/yuimodules' => 
    array (
      'mode' => 1,
      'component' => 'core',
      'area' => 'yuimodules',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/observers' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 2,
      'component' => 'core',
      'area' => 'observers',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/plugin_manager' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'plugin_manager',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/coursecattree' => 
    array (
      'mode' => 1,
      'staticacceleration' => true,
      'invalidationevents' => 
      array (
        0 => 'changesincoursecat',
      ),
      'component' => 'core',
      'area' => 'coursecattree',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/coursecat' => 
    array (
      'mode' => 2,
      'invalidationevents' => 
      array (
        0 => 'changesincoursecat',
        1 => 'changesincourse',
      ),
      'ttl' => 600,
      'component' => 'core',
      'area' => 'coursecat',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/coursecatrecords' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'invalidationevents' => 
      array (
        0 => 'changesincoursecat',
      ),
      'component' => 'core',
      'area' => 'coursecatrecords',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/coursesectionspreferences' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'simpledata' => false,
      'staticacceleration' => true,
      'component' => 'core',
      'area' => 'coursesectionspreferences',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/coursecontacts' => 
    array (
      'mode' => 1,
      'staticacceleration' => true,
      'simplekeys' => true,
      'ttl' => 3600,
      'component' => 'core',
      'area' => 'coursecontacts',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/courseeditorstate' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'courseeditorstate',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/repositories' => 
    array (
      'mode' => 4,
      'component' => 'core',
      'area' => 'repositories',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/externalbadges' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'ttl' => 3600,
      'component' => 'core',
      'area' => 'externalbadges',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/coursemodinfo' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'canuselocalstore' => true,
      'requirelockingbeforewrite' => true,
      'component' => 'core',
      'area' => 'coursemodinfo',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/userselections' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'userselections',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/completion' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'ttl' => 3600,
      'staticacceleration' => true,
      'staticaccelerationsize' => 2,
      'component' => 'core',
      'area' => 'completion',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/coursecompletion' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'ttl' => 3600,
      'staticacceleration' => true,
      'staticaccelerationsize' => 30,
      'component' => 'core',
      'area' => 'coursecompletion',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/navigation_expandcourse' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'navigation_expandcourse',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/suspended_userids' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'suspended_userids',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/roledefs' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 30,
      'component' => 'core',
      'area' => 'roledefs',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/plugin_functions' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'canuselocalstore' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 5,
      'component' => 'core',
      'area' => 'plugin_functions',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/tags' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'staticacceleration' => true,
      'component' => 'core',
      'area' => 'tags',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/grade_categories' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'invalidationevents' => 
      array (
        0 => 'changesingradecategories',
      ),
      'component' => 'core',
      'area' => 'grade_categories',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/temp_tables' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'temp_tables',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/tagindexbuilder' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simplevalues' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 10,
      'ttl' => 900,
      'invalidationevents' => 
      array (
        0 => 'resettagindexbuilder',
      ),
      'component' => 'core',
      'area' => 'tagindexbuilder',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/contextwithinsights' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 1,
      'component' => 'core',
      'area' => 'contextwithinsights',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/message_processors_enabled' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 3,
      'component' => 'core',
      'area' => 'message_processors_enabled',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/message_time_last_message_between_users' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simplevalues' => true,
      'datasource' => '\\core_message\\time_last_message_between_users',
      'component' => 'core',
      'area' => 'message_time_last_message_between_users',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/fontawesomeiconmapping' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 1,
      'component' => 'core',
      'area' => 'fontawesomeiconmapping',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/postprocessedcss' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => false,
      'component' => 'core',
      'area' => 'postprocessedcss',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/user_group_groupings' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'core',
      'area' => 'user_group_groupings',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/presignup' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simpledata' => true,
      'ttl' => 1800,
      'component' => 'core',
      'area' => 'presignup',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/modelfirstanalyses' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'modelfirstanalyses',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/portfolio_add_button_portfolio_instances' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'staticacceleration' => true,
      'component' => 'core',
      'area' => 'portfolio_add_button_portfolio_instances',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/course_user_dates' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'core',
      'area' => 'course_user_dates',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/calculablesinfo' => 
    array (
      'mode' => 4,
      'simplekeys' => false,
      'simpledata' => false,
      'component' => 'core',
      'area' => 'calculablesinfo',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/user_course_content_items' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'component' => 'core',
      'area' => 'user_course_content_items',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/user_favourite_course_content_items' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'component' => 'core',
      'area' => 'user_favourite_course_content_items',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/recommendation_favourite_course_content_items' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'component' => 'core',
      'area' => 'recommendation_favourite_course_content_items',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/contentbank_enabled_extensions' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'core',
      'area' => 'contentbank_enabled_extensions',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/contentbank_context_extensions' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'core',
      'area' => 'contentbank_context_extensions',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/h5p_content_type_translations' => 
    array (
      'mode' => 1,
      'simpledata' => true,
      'component' => 'core',
      'area' => 'h5p_content_type_translations',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/h5p_libraries' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'canuselocalstore' => true,
      'component' => 'core',
      'area' => 'h5p_libraries',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/h5p_library_files' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'canuselocalstore' => true,
      'component' => 'core',
      'area' => 'h5p_library_files',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/grade_letters' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 100,
      'component' => 'core',
      'area' => 'grade_letters',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/license' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => false,
      'component' => 'core',
      'area' => 'license',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/gradesetting' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 100,
      'component' => 'core',
      'area' => 'gradesetting',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/course_image' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'datasource' => '\\core_course\\cache\\course_image',
      'component' => 'core',
      'area' => 'course_image',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/contentbank_allowed_categories' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simpledata' => true,
      'invalidationevents' => 
      array (
        0 => 'changesincoursecat',
        1 => 'changesincategoryenrolment',
      ),
      'component' => 'core',
      'area' => 'contentbank_allowed_categories',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/contentbank_allowed_courses' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simpledata' => true,
      'invalidationevents' => 
      array (
        0 => 'changesincoursecat',
        1 => 'changesincategoryenrolment',
        2 => 'changesincourse',
      ),
      'component' => 'core',
      'area' => 'contentbank_allowed_courses',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/reportbuilder_allowed_reports' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'ttl' => 1800,
      'component' => 'core',
      'area' => 'reportbuilder_allowed_reports',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'core/file_imageinfo' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'canuselocalstore' => true,
      'staticaccelerationsize' => 100,
      'component' => 'core',
      'area' => 'file_imageinfo',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'availability_completion/previous_cache' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'availability_completion',
      'area' => 'previous_cache',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'availability_grade/scores' => 
    array (
      'mode' => 1,
      'staticacceleration' => true,
      'staticaccelerationsize' => 2,
      'ttl' => 3600,
      'component' => 'availability_grade',
      'area' => 'scores',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'availability_grade/items' => 
    array (
      'mode' => 1,
      'staticacceleration' => true,
      'staticaccelerationsize' => 2,
      'ttl' => 3600,
      'component' => 'availability_grade',
      'area' => 'items',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_assign/overrides' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'datasource' => '\\mod_assign\\cache\\overrides',
      'component' => 'mod_assign',
      'area' => 'overrides',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_bigbluebuttonbn/serverinfo' => 
    array (
      'mode' => 1,
      'invalidationevents' => 
      array (
        0 => 'mod_bigbluebuttonbn/serversettingschanged',
      ),
      'component' => 'mod_bigbluebuttonbn',
      'area' => 'serverinfo',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_bigbluebuttonbn/validatedurls' => 
    array (
      'mode' => 1,
      'simpledata' => true,
      'component' => 'mod_bigbluebuttonbn',
      'area' => 'validatedurls',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_bigbluebuttonbn/recordings' => 
    array (
      'mode' => 1,
      'invalidationevents' => 
      array (
        0 => 'mod_bigbluebuttonbn/recordingchanged',
        1 => 'mod_bigbluebuttonbn/serversettingschanged',
      ),
      'ttl' => 300,
      'component' => 'mod_bigbluebuttonbn',
      'area' => 'recordings',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_bigbluebuttonbn/currentfetch' => 
    array (
      'mode' => 4,
      'component' => 'mod_bigbluebuttonbn',
      'area' => 'currentfetch',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_forum/forum_is_tracked' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'mod_forum',
      'area' => 'forum_is_tracked',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_forum/forum_count_discussions' => 
    array (
      'mode' => 4,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'invalidationevents' => 
      array (
        0 => 'changesinforumdiscussions',
      ),
      'component' => 'mod_forum',
      'area' => 'forum_count_discussions',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_glossary/concepts' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => false,
      'staticacceleration' => true,
      'staticaccelerationsize' => 30,
      'component' => 'mod_glossary',
      'area' => 'concepts',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_lesson/overrides' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'datasource' => '\\mod_lesson\\cache\\overrides',
      'component' => 'mod_lesson',
      'area' => 'overrides',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_lti/keyset' => 
    array (
      'mode' => 1,
      'component' => 'mod_lti',
      'area' => 'keyset',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'mod_quiz/overrides' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'datasource' => '\\mod_quiz\\cache\\overrides',
      'component' => 'mod_quiz',
      'area' => 'overrides',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'block_quickmail/qm_msg_recip_count' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'block_quickmail',
      'area' => 'qm_msg_recip_count',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'block_quickmail/qm_msg_addl_email_count' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'block_quickmail',
      'area' => 'qm_msg_addl_email_count',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'block_quickmail/qm_msg_attach_count' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'block_quickmail',
      'area' => 'qm_msg_attach_count',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'block_quickmail/qm_msg_deliv_count' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'block_quickmail',
      'area' => 'qm_msg_deliv_count',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'block_quickmail/qm_controller_store' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'block_quickmail',
      'area' => 'qm_controller_store',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'block_quickmail/qm_event_notif_last_fired_at' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'component' => 'block_quickmail',
      'area' => 'qm_event_notif_last_fired_at',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'repository_googledocs/folder' => 
    array (
      'mode' => 1,
      'simplekeys' => false,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 10,
      'canuselocalstore' => true,
      'component' => 'repository_googledocs',
      'area' => 'folder',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'repository_onedrive/folder' => 
    array (
      'mode' => 1,
      'simplekeys' => false,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 10,
      'canuselocalstore' => true,
      'component' => 'repository_onedrive',
      'area' => 'folder',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_dataprivacy/purpose' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 30,
      'component' => 'tool_dataprivacy',
      'area' => 'purpose',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_dataprivacy/purpose_overrides' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => false,
      'staticacceleration' => true,
      'staticaccelerationsize' => 50,
      'component' => 'tool_dataprivacy',
      'area' => 'purpose_overrides',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_dataprivacy/contextlevel' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 10,
      'component' => 'tool_dataprivacy',
      'area' => 'contextlevel',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_iomadpolicy/iomadpolicy_optional' => 
    array (
      'mode' => 4,
      'component' => 'tool_iomadpolicy',
      'area' => 'iomadpolicy_optional',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_mobile/plugininfo' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 1,
      'component' => 'tool_mobile',
      'area' => 'plugininfo',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_mobile/subscriptiondata' => 
    array (
      'mode' => 2,
      'simplekeys' => true,
      'simpledata' => false,
      'component' => 'tool_mobile',
      'area' => 'subscriptiondata',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_monitor/eventsubscriptions' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 10,
      'component' => 'tool_monitor',
      'area' => 'eventsubscriptions',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_policy/policy_optional' => 
    array (
      'mode' => 4,
      'component' => 'tool_policy',
      'area' => 'policy_optional',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_uploadcourse/helper' => 
    array (
      'mode' => 4,
      'component' => 'tool_uploadcourse',
      'area' => 'helper',
      'sharingoptions' => 2,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_usertours/tourdata' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 1,
      'component' => 'tool_usertours',
      'area' => 'tourdata',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'tool_usertours/stepdata' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 1,
      'component' => 'tool_usertours',
      'area' => 'stepdata',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'theme_adaptable/adaptablefontawesomeiconmapping' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'staticaccelerationsize' => 1,
      'component' => 'theme_adaptable',
      'area' => 'adaptablefontawesomeiconmapping',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'local_iomad/allcompanycategories' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'invalidationevents' => 
      array (
        0 => 'changesincompanycourses',
      ),
      'component' => 'local_iomad',
      'area' => 'allcompanycategories',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'local_iomad/companycategories' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'invalidationevents' => 
      array (
        0 => 'changesincompanycourses',
      ),
      'component' => 'local_iomad',
      'area' => 'companycategories',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'local_iomad/companycoursecategories' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'invalidationevents' => 
      array (
        0 => 'changesincompanycourses',
      ),
      'component' => 'local_iomad',
      'area' => 'companycoursecategories',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'quizaccess_seb/quizsettings' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'quizaccess_seb',
      'area' => 'quizsettings',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'quizaccess_seb/config' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'quizaccess_seb',
      'area' => 'config',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
    'quizaccess_seb/configkey' => 
    array (
      'mode' => 1,
      'simplekeys' => true,
      'simpledata' => true,
      'staticacceleration' => true,
      'component' => 'quizaccess_seb',
      'area' => 'configkey',
      'sharingoptions' => 15,
      'selectedsharingoption' => 2,
      'userinputsharingkey' => '',
    ),
  ),
  'definitionmappings' => 
  array (
  ),
  'locks' => 
  array (
    'cachelock_file_default' => 
    array (
      'name' => 'cachelock_file_default',
      'type' => 'cachelock_file',
      'dir' => 'filelocks',
      'default' => true,
    ),
  ),
);