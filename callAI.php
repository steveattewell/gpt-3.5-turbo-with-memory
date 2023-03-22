<?php
/* 
OpenAI chat with a short-term memory
uses the gpt-3.5-turbo language model
By Steve Attewell https://twitter.com/steveattewell
Call this file like this: 
callAI.php?text=[the text you'd like a response from]
And it will echo a response from the AI.
You can optionally add ?forget=true to make the AI forget your previous responses before answering

*/


//start a session and make sure the user has come from this website ///

session_start();
$testmode = isset($_GET['testmode']) ? $_GET['testmode'] : null;
$forget = isset($_GET['forget']) ? $_GET['forget'] : null;
if($forget){
  // forget the previous interactions
    $_SESSION['conversations'] = null;
    echo "forgotten";
}
// hide all errors
//================================
/*
error_reporting(E_ALL);
// Define a custom error handler function
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    die("Sorry, something went wrong and I was unable to respond");

}
// Set the custom error handler function
set_error_handler("customErrorHandler");
*/


//// SECURITY CHECK COMPLETE

// SETUP VARIABLES:
// =======================
$openai_api_key = '[YOUR_API_KEY_GOES_HERE]'; // your OpenAI API key. Get one from https://platform.openai.com/
$number_of_interactions_to_remember = 5; // can be 0. Basically a "short term memory". Remembers the last n interactions. Allow you to ask follow-up questions like "tell me more about that" or "give me another example"



// Get the querystring parameters.


$text = isset($_GET['text']) ? $_GET['text'] : null;






if($text){


 //detect if there is some mention of date or time in the text:
  // ========================================================
    // Define the keywords to search for
    $keywords = array('time', 'date', 'day is it');

    // Check if the text contains any of the keywords
    $containsKeyword = false;
    $regex = '/\b(' . implode('|', $keywords) . ')\b/i';
    $containsKeyword = preg_match($regex, $text);
    
    // If the text contains a keyword, call the getWeather() function
    if ($containsKeyword) {
        $datetime = getDateTime(); 
        $text = "It is " . $datetime . ". If appropriate, respond to 
        the following in a short sentence: " . $text;
    }
    




  // set up a session variable to store the last n questions and responses
  if (!isset($_SESSION['conversations'])) {
    $_SESSION['conversations'] = array();
  }

  // Remove oldest conversation if the number oif interactions >= $number_of_interactions_to_remember 
  if (count($_SESSION['conversations']) > $number_of_interactions_to_remember + 1) {
    $_SESSION['conversations'] = array_slice($_SESSION['conversations'], -$number_of_interactions_to_remember, $number_of_interactions_to_remember, true);
  }


  // this is the main call we'll send to OpenAI
  $data = array(
    'model' => 'gpt-3.5-turbo',
    'messages' => array(
      array(
        'role' => 'system',
        'content' => 'You are called Chatty McChatface. 
        You give short, friendly reponses. '
      )
      
    )
  );

  // Adding to the call above, poke in the last 10 message history into the prompt we'll send to openAI
  foreach ($_SESSION['conversations'] as $conversation) {
    foreach ($conversation as $message) {
      array_push($data['messages'], array(
        'role' => $message['role'],
        'content' => $message['content']
      ));
      
      //echo '{"role": "' . $message['role'] . '", "content": "' . $message['content'] . '"},' . "\n";
    }
  }

  // and finally add the latest text from the user to the prompt we'll send to openAI chat gpt-3.5-turbo
  array_push($data['messages'], array(
    'role' => 'user',
    'content' => $text
  ));

// make the call to the OpenAI API
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => array(
      'Authorization: Bearer ' . $openai_api_key,
      'Content-Type: application/json'
    ),
  ));

  $response = curl_exec($curl);

  if($testmode){
    //write out the previous conversation
    echo json_encode($data);
    echo "<br><br>";
    //write out the latest response
    echo $response;
    echo "<br><br>";
  }
  $response = json_decode($response, true);
  
  curl_close($curl);

  if (isset($response['choices'][0]['message']['content'])) {
    // The key exists, do something here
    $content = $response['choices'][0]['message']['content'];
  } else {
      // The key doesn't exist
      $content = "Something went wrong! ```" . json_encode($response) . "```";
  }
  


  // Add new conversation to end of the conversation array (we'll use this in the next prompt so that the AI has a short term memory of our last 10 interactions)
  
  //make a note of the questions we were just asked, and the response we got back from OpenAI
  $new_conversation = array(
    array(
      'role' => 'user',
      'content' => $text
    ),
    array(
      'role' => 'assistant',
      'content' => $content
    )
  );

  //and push that into our "memory" of the last few interactions (dictated by $number_of_interactions_to_remember ).
  array_push($_SESSION['conversations'], $new_conversation);


  echo $content;
}

?>
