#!/usr/bin/php
<?php
/**
 * USAGE: ./extract_chart_configs.php -u <username>
 * Saved metric explorer charts are stored in each user's profile. The profile is stored as a
 * serialized PHP object with a 'queries_store' property containing a 'data' array that stores
 * objects describing each chart. The 'data' array contains the chart name and a 'config'
 * property containing an encoded JSON representation of the chart configuration. For example:
 *
 * {
 *   'queries_store' => {
 *     'data' => [
 *       {
 *         'name' => 'my chart',
 *         'config' => '{"featured":false,"trend_line":false, ...'
 *       }
 *     ]
 *   }
 * }
 *
 * This tool provides a means to extract individual chart configurations inorder to make the 
 * metric explorer charts in a user's profile public and make them available as default summary
 * charts. These chart configs are to be added to /etc/xdmod/roles.json under appropriate ACLs 
 */


#Parse the cmd line args
$options = getopt("u:");

if (count($options) != 1) {
  echo "Usage: ./$argv[0] -u <username> \n\n"; 
  exit;
} 
else {
  $user = $options["u"];
}

# Turn on mysqli error reporting for queries, mysqli fails silently by default.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

# Mysql DB connection object
$mysqli = new mysqli("localhost", "root", "", "");

# Execute the query and generate the result set object
$result = $mysqli->query("SELECT serialized_profile_data FROM moddb.UserProfiles INNER JOIN moddb.Users ON moddb.UserProfiles.user_id=moddb.Users.id AND moddb.Users.username='$user';");

# Convert the result set object into an associative array to access its values
$data = $result->fetch_assoc();
#printf("%s (%s)\n", $data['serialized_profile_data'], gettype($data['serialized_profile_data']));

# Convert serialized value obtained above to a php value
$profile = unserialize($data['serialized_profile_data']);

# Convert the PHP object into JSON object and decode it into an array of chart configs
$queries = json_decode(json_encode($profile['queries_store']['data']), True);

# Parse through each metric_explorer chart config and display
foreach ($queries as $name => $query) {
    echo "\nChart Name ==> ";
    echo $query['name'], ":\n";
    echo $query['config'], "\n\n";
}

?>
