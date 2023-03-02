# gpt-3.5-turbo-with-memory
A vanilla PHP script that interacts with OpenAI's gpt-3.5-turbo API and retains a short-term memory of your last few interactions

## More about the ChatGPT API here
https://platform.openai.com/docs/guides/chat/introduction

## What deos this script do?
Host this script on your hosting provider fo choice and call it using:

```callAI.php?text=[the quesiton youd like to ask the API]```

and the script will return an answer.

Remember to place your OpenAI API Key in the script at ```$openai_api_key```. You can get an API key from OpenAI here: http://platform.openai.com

## It has a short term memory
The script saves the last few interactions you had with the AI in a session variable and uses your chat history in subsequent calls to the AI. Again, there's more here about that https://platform.openai.com/docs/guides/chat/introduction 

You can change the number of interactions to remember here ```$number_of_interactions_to_remember```

I can't find any guidance on how many interactions to remember if optimal, or how it affects costs. You're on your own there!


