# Gravity Forms Notion Add-on

Connects Gravity Forms to Notion Databases. Send the form data to Notion databases when the form is submitted on the website.

## Requirements
- Notion API key ( You can get it from a Notion integration ).

- To list all databases under an API key, make sure you have associated that particular database with the provided API key's integration.

> @see https://developers.notion.com/docs/getting-started#getting-started

## Notion database fields mapping with GF fields
- Notion field with property type `Title` is `required` i.e. this field needs a GF field to be mapped with.

- Notion Add-On now supports 9 Notion database basic properties out of 11.

  - [Check GF Notion Add-On Properties Support](assets/images/gf-notion-1.png)

  - No support for person field as we can not identify who is the person and whether this person exists in out Notion workspace or not.
  - No support for checkbox as it only supports boolean values. During feed processing we don't get the value of checkbox field as boolean.


## Terms to remember
- Validate Notion API Key - Validates whether the API key provided is valid or not.

- Get database list - Get all the databases accessed from the provided API key.

- Get database fields - Get all the fields ( or properties ) in a database.

- Create Page - Create an entry in the Notion database.

- Log Debug - Log debug message to the add-on log.

- Log Error - Log error message to the add-on log.
> Logging is available only if logging is enabled in Gravity Forms settings.

## Debugging
- During working on this Add-on please make sure that you have enabled the logging in the Gravity Forms settings.

- All debug logs and error logs can be found within the add-on logs.

- If any new method or function is added then make sure you have added proper functionality to add logs for that particular functionality.
