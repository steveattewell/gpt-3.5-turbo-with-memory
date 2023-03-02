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


// SETUP VARIABLES:
// =======================
$openai_api_key = 'sk-[INSERT-YOUR-API-KEY-HERE]'; // your OpenAI API key. Get one from https://platform.openai.com/
$number_of_interactions_to_remember = 10; // can be 0. Basically a "short term memory". Remembers the last n interactions. Allow you to ask follow-up questions like "tell me more about that" or "give me another example"

// Get the querystring parameters.

$forget = isset($_GET['forget']) ? $_GET['forget'] : null;
$text = isset($_GET['text']) ? $_GET['text'] : null;


if($forget){
  // forget the previous interactions
    $_SESSION['conversations'] = null;
}
if($text){
  // set up a session variable to store the last n questions and responses
  if (!isset($_SESSION['conversations'])) {
    $_SESSION['conversations'] = array();
  }

  // Remove oldest conversation if the number oif interactions >= $number_of_interactions_to_remember 
  if (count($_SESSION['conversations']) >= $number_of_interactions_to_remember) {
    array_shift($_SESSION['conversations']);
  }


  // this is the main call we'll send to OpenAI
  $data = array(
    'model' => 'gpt-3.5-turbo',
    'messages' => array(
      array(
        'role' => 'system',
        'content' => 'You are a helpful assistant called Frank that gives short, friendly reponses.'
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
  //echo $response;
  $response = json_decode($response, true);
  curl_close($curl);
  $content = $response['choices'][0]['message']['content'];


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
