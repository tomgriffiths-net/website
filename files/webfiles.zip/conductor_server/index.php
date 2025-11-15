<?php
require '../../localfiles/global.php';

$packages = runfunction('pkgmgr::getLoadedPackages();');

html::fullhead("conductor_server","Conductor Server");

if(!isset($packages['conductor_server'])){
    html::loadurl('/');
}

if($packages['conductor_server'] > 9){//v10 introduced getJobTimes
    $jobTimes = runfunction('conductor_server::getJobTimes(5)');
    if(is_array($jobTimes)){
        echo '
            <div class="d-flex" style="width:calc(100% - 60px); height:50px; margin:10px; margin-left:30px; margin-right:30px; background-color:#444; border-radius:5px; align-items:center; justify-content:center; gap:5%; padding-top:6px;">
                <div><h4>Average job time: ' . round($jobTimes['average_job_time'] / 60) . 'm</h4></div>
                <div><h4>Current speed: ' . $jobTimes['jobs_per_day_estimate'] . ' jobs/day</h4></div>
                <div><h4>Estimated time left: ' . round($jobTimes['time_left_estimate'] / 3600) . 'H</h4></div>
                <div><h4>Estimated finish time: ' . $jobTimes['finish_time_estimate'] . '</h4></div>
            </div>
        ';
    }
}

echo '<div style="margin-top:10px; margin-left:10px; display:flex; width:calc(100% - 10px);">';

$loops = 0;
foreach([
    'Successful jobs:' => '\'$successful && $count < 20\', 0, true',
    'Failed jobs:'     => '\'$failed && $count < 20\', 0, true',
    'Processing jobs:' => '\'$processing && $count < 20\', 0, false',
    'Pending jobs:'    => '\'!$taken && $count < 20\', 0, false'
] as $colName => $colExp){
    $results = runfunction('conductor_server::filterJobs(' . $colExp . ')');
    if(is_array($results)){
        if($loops === 0){
            $colName .= ' ' . $results['totals']['successful'];
        }
        elseif($loops === 1){
            $colName .= ' ' . $results['totals']['failed'];
        }
        elseif($loops === 2){
            $colName .= ' ' . $results['totals']['processing'];
        }
        elseif($loops === 3){
            $colName .= ' ' . ($results['totals']['totaljobs'] - $results['totals']['taken']);
        }
        echo '<div style="width:25%; margin-right:10px;"><h3>' . $colName . '</h3>';
        $loops++;
        foreach($results['jobs'] as $job){
            $icon = 'question.svg';
            if(substr($job['action'],0,15) === "video_encoder::"){
                $icon = 'movie.svg';
            }
            elseif($job['action_type'] === "function_string"){
                $icon = 'fx.svg';
            }
            echo '
                <div style="width:100%; margin-right:10px; height:100px; margin-bottom:10px; display:flex; border-radius:10px; position:relative; overflow:hidden; background:linear-gradient(to right, #555, #555, ';
                    if(!$job['requested'] && !$job['completed']){
                        echo '#1643b5';
                    }
                    elseif($job['requested'] && !$job['completed']){
                        echo '#b5b516';
                    }
                    elseif($job['requested'] && $job['completed']){
                        if($job['return'] || $job['finish_function_return']){
                            echo '#18ab13';
                        }
                        else{
                            echo '#c21906';
                        }
                    }
                    else{
                        echo '#555';
                    }
                    echo ');">

                    <div style="width:80px; position:relative;">
                        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:60px; height:60px; border-radius:30px; background-color:#333;">
                            <div style="position:absolute; top:5px; left:5px; width:50px; height:50px; border-radius:25px; background-color:#dc0;">
                                <img src="' . $filesUrl . '/img/' . $icon . '" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); width:35px; height:auto;">
                            </div>
                        </div>
                    </div>

                    <div style="width:calc(100% - 80px); overflow:hidden; padding-top:5px;">
                        <span style="white-space:nowrap; line-height:28px;">
                            Type: ' . $job['action_type'] . '<br>
                            ';
                            if($job['action_type'] === "function_string"){
                                echo 'Function: ' . substr($job['action'],0,strpos($job['action'],"(")) . '<br>';
                                if(substr($job['action'],0,27) === "video_encoder::encode_video"){
                                    $videoPath = getFirstArgument($job['action']);
                                    if(is_string($videoPath)){
                                        $videoPath = basename($videoPath);
                                        if(strlen($videoPath) > 37){
                                            $videoPath = substr($videoPath,0,35) . '...';
                                        }
                                        echo 'Video: ' . $videoPath . '<br>';
                                    }
                                }
                            }
                            echo '
                        </span>
                    </div>
                </div>
            ';
        }
    }
    else{
        echo "Error getting jobs";
    }

    echo '</div>';
}

echo '</div>';

html::fullend();

function getFirstArgument(string $functionCall):string|false{
    $openParenPos = strpos($functionCall, '(');
    if ($openParenPos === false) {
        return false;
    }

    $index = $openParenPos + 1;
    $length = strlen($functionCall);
    
    // Skip whitespace after opening parenthesis
    while ($index < $length && ctype_space($functionCall[$index])) {
        $index++;
    }
    
    // Check for opening quote
    if ($index >= $length || ($functionCall[$index] !== '"' && $functionCall[$index] !== "'")) {
        return false;
    }
    
    $quoteChar = $functionCall[$index];
    $index++; // Move past opening quote
    $result = '';
    $escaped = false;

    while ($index < $length) {
        $char = $functionCall[$index];
        
        if ($escaped) {
            $result .= $char;
            $escaped = false;
        } elseif ($char === '\\') {
            $escaped = true;
        } elseif ($char === $quoteChar) {
            break;
        } else {
            $result .= $char;
        }
        
        $index++;
    }
    
    return $result;
}