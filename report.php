<?php

// This script outputs a cvs file of summarizing the commits by each contributor for each of the projects in the 
// uPortal Ecosystem over a given date range.

ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)'); 
$branch="master";
$page="1";
$since="2021-01-01";
$until="2021-12-31";

// The list of repositories in the uPortal-Project Github organization that we wish to include
$projects = array("uPortal", "uPortal-start", "uPortal-web-components", "uportal-project.github.io", "notification-web-components", "uportal-app-framework", "uportal-home", "esup-filemanager", "FeedbackPortlet", "SimpleContentPortlet", "AnnouncementsPortlet", "CalendarPortlet", "EsupTwitter", "CalendarPortlet", "BookmarksPortlet", "CoursesPortlet", "email-preview", "JasigWidgetPortlets", "MapPortlet", "NewsReaderPortlet", "NotificationPortlet", "SurveyPortlet", "WeatherPortlet", "WebproxyPortlet", "portlet-utils", "basiclti-portlet", "ContactsPortlet", "ClassifiedsPortlet");

// To not exceed rate limits, you must enter your Github username and token
$username="";
$token="";

$context = stream_context_create(array(
    'http' => array(
        'header'  => "Authorization: Basic " . base64_encode("$username:$token")
    )
));

$committers = array();
$total_project_commits=0;
$total_commits=0;

class User {
  public $name;
  public $email;
  public $data;
  public $project_commits;
  public $total_commits;
}

function add_user($name, $email, $date) {
  global $committers;
  global $total_project_commits;
  global $total_commits;

  $exists = 0;
  for ($i = 0; $i < count($committers); $i++) {
      if ($committers[$i]->email == $email || $committers[$i]->name == $name) {
          $exists = 1;
          $committers[$i]->project_commits = $committers[$i]->project_commits + 1;
          $committers[$i]->total_commits = $committers[$i]->total_commits + 1;
      }
  }
  if ($exists == 0) {
    $user = new User;
    $user->name = $name;
    $user->email = $email;
    $user->date = $date;
    $user->project_commits = $user->project_commits+1;
    $user->total_commits = $user->total_commits+1;
    array_push($committers, $user);
  }

  $total_project_commits = $total_project_commits + 1;
  $total_commits = $total_commits + 1;
}

echo "uPortal Ecosystem Commits from " . $since . " to " . $until . "\n";

for ($j = 0; $j < count($projects); $j++) {
    $receiving = true;
    $page = 1;

    while($receiving) {
      $url = "https://api.github.com/repos/uPortal-Project/".$projects[$j]."/commits?since=$since&until=$until&per_page=100&page=$page&sha=master";
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

    for ($i = 0; $i < count($committers); $i++) {
        print_r($projects[$j] . ", " . $committers[$i]->name . ", " . $committers[$i]->project_commits . "\n");
        $committers[$i]->project_commits = 0;
    }
    print_r($projects[$j] . ", Total, " . $total_project_commits . "\n");
    $total_project_commits = 0;
}

for ($i = 0; $i < count($committers); $i++) {
  print_r("uPortal Ecosystem, " . $committers[$i]->name . ", " . $committers[$i]->total_commits . "\n");
}
echo "uPortal Ecosystem, Total, " . $total_commits . "\n";

?>
