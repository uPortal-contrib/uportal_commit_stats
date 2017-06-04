<?php

ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)'); 
$branch="master";
$page="1";
$since="2016-06-01";
$until="2017-07-01";
$portlets = array("FeedbackPortlet", "SimpleContentPortlet", "AnnouncementsPortlet", "CalendarPortlet", "EsupTwitter", "CalendarPortlet", "BookmarksPortlet", "CoursesPortlet", "email-preview", "JasigWidgetPortlets", "MapPortlet", "NewsReaderPortlet", "NotificationPortlet", "SurveyPortlet", "WeatherPortlet", "WebproxyPortlet", "portlet-utils", "basiclti-portlet", "ContactsPortlet", "ClassifiedsPortlet");

$username="";
$token="";

$context = stream_context_create(array(
    'http' => array(
        'header'  => "Authorization: Basic " . base64_encode("$username:$token")
    )
));

$committers = array();

class User {
  public $name;
  public $email;
  public $data;
  public $commits;
}

function add_user($name, $email, $date) {
  global $committers; 
  $exists = 0;
  for ($i = 0; $i < count($committers); $i++) {
      if ($committers[$i]->email == $email || $committers[$i]->name == $name) {
          $exists = 1;
          $committers[$i]->commits = $committers[$i]->commits + 1;
      }
  }
  if ($exists == 0) {
    $user = new User;
    $user->name = $name;
    $user->email = $email;
    $user->date = $date;
    $user->commits = $user->commits+1;
    array_push($committers, $user);
  }
}

$receiving = true;
$page = 1;

while($receiving) {
  $url = "https://api.github.com/repos/jasig/uPortal/commits?since=$since&until=$until&per_page=100&page=$page&sha=$branch";
  $results = file_get_contents($url, false, $context);
  $json_results = json_decode($results);
  if (count($json_results) > 0) {
    for ($i = 0; $i < count($json_results); $i++) {
      $user = add_user($json_results[$i]->commit->author->name, $json_results[$i]->commit->author->email, $json_results[$i]->commit->author->date);   
    }
  } else {
    $receiving = false;
  }
  $page++;
}

echo "Master branch committers\n";
for ($i = 0; $i < count($committers); $i++) {
  print_r($committers[$i]->name . " " . $committers[$i]->commits . "\n");
}

$receiving = true;
$page = 1;

while($receiving) {
  $url = "https://api.github.com/repos/jasig/uPortal/commits?since=$since&until=$until&per_page=100&page=$page&sha=rel-4-3-patches";
  $results = file_get_contents($url, false, $context);
  $json_results = json_decode($results);
  if (count($json_results) > 0) {
    for ($i = 0; $i < count($json_results); $i++) {
      $user = add_user($json_results[$i]->commit->author->name, $json_results[$i]->commit->author->email, $json_results[$i]->commit->author->date);   
    }
  } else {
    $receiving = false;
  }
  $page++;
}

echo "\n\nMaster and rel-4-3-patches branch committers\n";
for ($i = 0; $i < count($committers); $i++) {
  print_r($committers[$i]->name . " " . $committers[$i]->commits . "\n");
}

$committers = array();

for ($j = 0; $j < count($portlets); $j++) {
    $receiving = true;
    $page = 1;

    while($receiving) {
      $url = "https://api.github.com/repos/jasig/".$portlets[$j]."/commits?since=$since&until=$until&per_page=100&page=$page&sha=master";
      $results = file_get_contents($url, false, $context);
      $json_results = json_decode($results);
      if (count($json_results) > 0) {
        for ($i = 0; $i < count($json_results); $i++) {
          $user = add_user($json_results[$i]->commit->author->name, $json_results[$i]->commit->author->email, $json_results[$i]->commit->author->date);   
        }
      } else {
        $receiving = false;
      }
      $page++;
    }
}

echo "\n\nuPortal Portlet and Supporting Artifact committers\n";
for ($i = 0; $i < count($committers); $i++) {
  print_r($committers[$i]->name . " " . $committers[$i]->commits . "\n");
}

?>
