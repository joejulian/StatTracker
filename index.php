<?php
require_once("config.php");
require_once("code/StatTracker.class.php");
//require_once("code/Agent.class.php");
require_once("code/Authentication.class.php");
require_once("vendor/autoload.php");

const ENL_GREEN = "#2BED1B";
const RES_BLUE = "#00BFFF";

use Symfony\Component\HttpFoundation\Response;

$mysql = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysql->connect_errno) {
	die(sprintf("%s: %s", $mysql->connect_errno, $mysql->connect_error));
}

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__ . "/views",
));

$agent = new BlueHerons\StatTracker\Agent();
if ($app['session']->get("agent") !== null) {
	$agent = $app['session']->get("agent");
	$agent = \BlueHerons\StatTracker\Agent::createFrom($agent);
}

$navbar_options = array(
	"app_name" => "Stat Tracker",
	"app_url" => "http://api.johnluetke.net/ingress/stats",
	"base_url" => "http://api.johnluetke.net/ingress"
);

// Default handler. Will match any alphnumeric string. If the page doesn't exist,
// 404
$app->get('/{page}', function ($page) use ($app) {
	if ($page == "dashboard" ||
	    $page == "submit-stats" ||
	    $page == "leaderboards") {
		$navbar = BlueHerons\Common\Navbar::create($navbar_options);
		$leaderboard_links = array();
		foreach (StatTracker::getStats() as $stat) {
			$leaderboard_links[$stat->name] = "./leaderboards#" .$stat->stat;
		}

		return $app['twig']->render("index.twig", array(
			"navbar_css" => $navbar->renderCSS(false),
			"navbar_js" => $navbar->renderJS(false),
			"navbar_html" => $navbar->render(
						"Stat Tracker", 
						null, 
						array(
							array(	"Dashboard" => "./dashboard", 
								"My Stats" => "./my-stats", 
								"Leaderboards" => $leaderboard_links)
						), 
						false),
			"page" => $page
		));
	}
	else if ($page == "terms-of-use") {
		return $app['twig']->render("terms.html");
	}
	else if ($page == "authenticate") {
		switch ($_REQUEST['action']) {
			case "login":
				return $app->json(Authentication::getInstance()->login());
				break;
			case "callback":
				if (Authentication::getInstance()->callback()) {
					return $app->redirect("./dashboard");
				}
				else {
					$app->abort(500, "An error occured during authentication");
				}
				break;
			case "logout":
				return $app->json(Authentication::getInstance()->logout());
				break;
			default:
				$app->abort(405, "Invalid Authentication action");
		}
	}
	else {
		$app->abort(404);
	}
})->assert('page', '[a-z-]+')
  ->value('page', 'dashboard');

$app->get('/page/{page}', function($page) use ($app, $agent) {
	if ($page == "submit-stats") {
		$agent->getLatestStats(true);
	}

	return $app['twig']->render($page.".twig", array(
		"agent" => $agent,
		"stats" => StatTracker::getStats(),
		"faction_class" => $agent->faction == "R" ? "resistance-agent" : "enlightened-agent",
		"faction_color" => $agent->faction == "R" ? RES_BLUE : ENL_GREEN,
		"message" => ""
	));
});

$app->run();
?>
