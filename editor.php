<?php 
$db = new SQLite3('api.db');

$id =  "";
$name =  "";
$path =  "";

$version =  "";
$implemented =  "";
$requested =  "";
$oneliner =  "";

$desc =  "";
$verb =  "";
$expects =  "";

$returns =  "";
$filters =  "";
$documentation = "";

$output = false;

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    if (isset($_POST["action"]))
    {
        $id = intval($_POST["del-id"]);
        $db->exec("DELETE FROM definitions WHERE id=$id");
    }
    else
    {
        $id = intval($_POST["id"]);
        $name = sqlite_escape_string($_POST["name"]);
        $path = sqlite_escape_string($_POST["path"]);

        $version = sqlite_escape_string($_POST["version"]);

        $implemented = 0;
        if (isset($_POST["implemented"]))
        {
            if(strtoupper($_POST["implemented"]) == "TRUE"  )
            {
                $implemented = 1;
            }
        }


        $requested = 0;
        if (isset($_POST["requested"]))
        {
            if(strtoupper($_POST["requested"]) == "TRUE"  )
            {
                $requested = 1;
            }
        }

        $oneliner = sqlite_escape_string($_POST["oneliner"]);

        $desc = sqlite_escape_string($_POST["desc"]);
        $verb = sqlite_escape_string(strtoupper($_POST["verb"]));
        $expects = sqlite_escape_string($_POST["expects"]);

        $returns = sqlite_escape_string($_POST["returns"]);
        $filters = sqlite_escape_string($_POST["filters"]);
        $documentation = sqlite_escape_string($_POST["documentation"]);


        if ($id > 0)
        {
            // Update Old
            $db->exec("UPDATE definitions SET name = '$name', path = '$path', version = '$version', implemented = $implemented, requested = $requested, oneliner = '$oneliner', desc = '$desc', verb = '$verb', expects = '$expects', returns = '$returns', filters = '$filters', documentation ='$documentation' WHERE id = $id");
        } else {
            // Insert New
            $db->exec("INSERT INTO definitions (name, path, version, implemented, oneliner, desc, verb, expects, returns, filters, documentation, requested ) VALUES ( '$name', '$path', '$version', $implemented, '$oneliner', '$desc', '$verb', '$expects', '$returns', '$filters', '$documentation', '$requested' )");
        }

        $output = true;
    }
}

$results = $db->query('SELECT * FROM definitions ORDER BY path DESC');
$jsonArr = array();
$endpoints = '';

while ($row = $results->fetchArray()) {
    array_push($jsonArr, array(
        "id" => $row["id"], 
        "name" => $row["name"], 
        "path" => $row["path"], 
        "version" => $row["version"], 
        "implemented" => $row["implemented"], 
        "requested" => $row["requested"], 
        "oneliner" => $row["oneliner"], 
        "desc" => $row["desc"], 
        "verb" => $row["verb"], 
        "expects" => $row["expects"], 
        "returns" => $row["returns"], 
        "filters" => $row["filters"], 
        "documentation" => $row["documentation"]));
    $endpoints .= '<option id="' . htmlspecialchars($row['id']) . '" onclick="view(' . htmlspecialchars($row['id']) . ');" data-documentation="' . htmlspecialchars($row["documentation"]) . '" data-filters="' . htmlspecialchars($row['filters']) . '" data-returns="' . htmlspecialchars($row['returns']) . '" data-expects="' . htmlspecialchars($row['expects']) . '" data-verb="' . htmlspecialchars($row['verb']) . '" data-desc="' . htmlspecialchars($row['desc']) . '" data-oneliner="' . htmlspecialchars($row['oneliner']) . '" data-implemented="' . htmlspecialchars($row['implemented']) . '" data-requested="' . htmlspecialchars($row['requested']) . '" data-name="' . htmlspecialchars($row['name']) . '"  data-id="' . htmlspecialchars($row['id']) . '" data-path="' . htmlspecialchars($row['path']) . '" data-version="' . htmlspecialchars($row['version']) . '">' . htmlspecialchars($row['name']) . '</option>';
}

if ($output)
{
    file_put_contents('endpoints.json', json_encode($jsonArr));
}

?><!DOCTYPE html>
<html lang="en">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>API Description Tool</title>

    <link href="bootstrap.css" rel="stylesheet">

    <script>
    function view(id)
    {
        var opt = document.getElementById(id);
        document.getElementById('id').value = opt.getAttribute("data-id");
         document.getElementById('del-id').value = opt.getAttribute("data-id");
        document.getElementById('name').value =  opt.getAttribute("data-name");
        document.getElementById('path').value =  opt.getAttribute("data-path");
        document.getElementById('version').value =  opt.getAttribute("data-version");
        if (opt.getAttribute("data-implemented") == 1)
        {
            document.getElementById('implemented').setAttribute("checked", "checked");
        } else {
            document.getElementById('implemented').removeAttribute("checked");
        }

        if (opt.getAttribute("data-requested") == 1)
        {
            document.getElementById('requested').setAttribute("checked", "checked");
        } else {
            document.getElementById('requested').removeAttribute("checked");
        }     

        document.getElementById('oneliner').value =  opt.getAttribute("data-oneliner");
        document.getElementById('desc').value =  opt.getAttribute("data-desc");

        document.getElementById('verb').value = opt.getAttribute("data-verb");

        document.getElementById('expects').value = opt.getAttribute("data-expects");
        document.getElementById('returns').value =  opt.getAttribute("data-returns");
        document.getElementById('filters').value =  opt.getAttribute("data-filters");
        document.getElementById('documentation').value =  opt.getAttribute("data-documentation");
    }
    </script>

    </head>
	<body>

        <div class="container">
            <div id="row">
                <P><p><small><a href="index.html">Overview Page</a></p></a>
                <div class="span6">
                    <h3>Method List</h3>
                    <select multiple="mulitple" id="method-list" style="height:700px;">
                        <?php echo $endpoints; ?>
                    </select>
                    <form id="second" name="second" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal" method="post" >
                        <ol class="semantic-form" start="1">
                            <li>
                                <input type="hidden" value="delete" name="action" />
                                <input type="hidden" value="delete" id="del-id" name="del-id" value="<?php echo htmlspecialchars($id); ?>" />
                                <input type="submit" value="delete" />
                            </li>
                        </ol>
                    </form>
                </div>
                <div class="span5">
                    <form id="main" name="main" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal" method="post" >
                        <ol class="semantic-form" start="1">
                            <li>
                                <fieldset>
                                    <legend>Details</legend>
                                    <ol class="semantic-inputs">
                                        <li class="control-group">
                                            <label for="id" class="control-label">Record Id</label>
                                            <div class="controls">
                                                <input type="text" id="id" name="id" class="" value="<?php echo htmlspecialchars($id); ?>" >
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="name" class="control-label">Name</label>
                                            <div class="controls">
                                              <input type="text" id="name" name="name" class="" value="<?php echo htmlspecialchars($name); ?>" >
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="path" class="control-label">Endpoint URL</label>
                                            <div class="controls">
                                               <input type="text" id="path" name="path" class="" value="<?php echo htmlspecialchars($path); ?>" >
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="version" class="control-label">Version Number</label>
                                            <div class="controls">
                                               <input type="text" id="version" name="version" class="" value="<?php echo htmlspecialchars($version); ?>" >
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="implemented" class="control-label">Is Implemented?</label>
                                            <div class="controls">
                                               <input type="checkbox" id="implemented" name="implemented" class="" value="true" >
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="requested" class="control-label">Feature Request?</label>
                                            <div class="controls">
                                               <input type="checkbox" id="requested" name="requested" class="" value="true" >
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="oneliner" class="control-label">One Line Description</label>
                                            <div class="controls">
                                              <input type="text" id="oneliner" name="oneliner" class="" value="<?php echo htmlspecialchars($oneliner); ?>" >
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="desc" class="control-label">Long Description</label>
                                            <div class="controls">
                                              <input type="text" id="desc" name="desc" class="" value="<?php echo htmlspecialchars($desc); ?>" >
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="verb" class="control-label">HTTP Verb</label>
                                            <div class="controls">
                                                <select id="verb" name="verb">
                                                    <option value="DELETE" <?php if ($verb == "DELETE") { echo 'select="selected"';} ?>>Delete</option>
                                                    <option value="GET" <?php if ($verb == "GET") { echo 'select="selected"';} ?>>Get</option>
                                                    <option value="POST" <?php if ($verb == "POST") { echo 'select="selected"';} ?>>Post</option>
                                                    <option value="PUT" <?php if ($verb == "PUT") { echo 'select="selected"';} ?>>Put</option>             
                                                </select>
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="expects" class="control-label">JSON Expected</label>
                                            <div class="controls">
                                                <textarea type="text" id="expects" name="expects" class=""><?php echo htmlspecialchars($expects); ?></textarea>
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="returns" class="control-label">JSON Return</label>
                                            <div class="controls">
                                                <textarea type="text" id="returns" name="returns" class=""><?php echo htmlspecialchars($returns); ?></textarea>
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="filters" class="control-label">Parametre Filters</label>
                                            <div class="controls">
                                                <textarea type="text" id="filters" name="filters" class=""><?php echo htmlspecialchars($filters); ?></textarea>
                                            </div>
                                        </li>
                                        <li class="control-group">
                                            <label for="documentation" class="control-label">Full documentaion URL</label>
                                            <div class="controls">
                                                <input type="text" id="documentation" name="documentation" class="" value="<?php echo htmlspecialchars($documentation); ?>" >
                                            </div>
                                        </li>
                                    </ol>
                                </fieldset>
                            </li>
                            <li>
                                <input type="submit" />
                            </li>
                        </ol>
                    </form>
                </div>
            </div>

    </body>
</html>
