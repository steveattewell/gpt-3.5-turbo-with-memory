# gpt-3.5-turbo-with-memory
A vanilla PHP script that interacts with OpenAI's gpt-3.5-turbo API and retains a short-term memory of your last few interactions

## More about the ChatGPT API here
https://platform.openai.com/docs/guides/chat/introduction

## How to use the script?

Host this PHP script on your hosting provider of choice and call it using:

```callAI.php?text=[the question you would like to ask the AI]```

and the script will return an answer.

Remember to place your OpenAI API Key in the script at ```$openai_api_key```. You can get an API key from OpenAI here: http://platform.openai.com

## It has a short term memory
The script saves the last few interactions you had with the AI in a session variable and uses your chat history in subsequent calls to the AI. Again, there's more here about that https://platform.openai.com/docs/guides/chat/introduction 

This short-term memory allows you to ask follow-up questions like "tell me more about that" or "give me another example" and the AI will consider the last few interactions you had with it in it's response.

The 'memory' basically builds a longer and longer version of this call to the API so that the API takes into account the last few interactions with it's next reponse.

<img width="744" alt="The format of a request to the API" src="https://user-images.githubusercontent.com/21079244/222454340-62389145-bcaa-4e26-870e-76cfb03398bd.png">

You can change the number of interactions to remember here ```$number_of_interactions_to_remember```

You can make it forget previous interactions before answering by setting the ```forget``` querystring parameter to anything.

```callAI.php?forget=true&text=[the question you would like to ask the AI]```


I can't find any guidance on how many interactions to remember if optimal, or how it affects costs. You're on your own there!

## Security considerations 
There are none in this script. If someone finds your hosted file they can start poking questions at it, getting responses, and spending your money at OpenAI. Consider this before using this script for anything live.
