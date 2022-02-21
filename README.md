# phpauth

Based on:

<https://docs.microsoft.com/en-us/azure/app-service/tutorial-auth-aad?pivots=platform-linux>

## Steps to do

```bash
az group create --name phpauth --location "West Europe"

az appservice plan create --name plan1 --resource-group phpauth --sku S1 --is-linux

az webapp create --resource-group phpauth --plan plan1 --name thx1140front --runtime "PHP|7.4" 

az webapp create --resource-group phpauth --plan plan1 --name thx1140back --runtime "PHP|7.4" 

rm back.zip
zip -j back.zip back/*.*
az webapp deployment source config-zip --resource-group phpauth --name thx1140back --src back.zip

rm front.zip
zip -j front.zip front/*.*
az webapp deployment source config-zip --resource-group phpauth --name thx1140front --src front.zip
```

MANUAL: enable auth on thx1140back

MANUAL enable auth on thx1140front

front app id: eaf8e871-0239-4b27-825b-131bab583010

back  app id: fee96351-9eda-4fb7-a91b-ac46e9c07358

MANUAL: give frontend api permissons on backend (App Registration, 'frontend': API Permissions)

MANUAL: add front-end client app id to backend trusted clients (App Registration, 'backend': Expose an API )
configure auth method on frontend to issue token for backend audience

```bash
authSettings=$(az webapp auth show -g phpauth -n thx1140front)
authSettings=$(echo "$authSettings" | jq '.properties' | jq '.identityProviders.azureActiveDirectory.login += {"loginParameters":["scope=openid profile email offline_access api://fee96351-9eda-4fb7-a91b-ac46e9c07358/user_impersonation"]}')
echo $authSettings
az webapp auth set --resource-group phpauth --name thx1140front --body "$authSettings"
```

```bash
id=$(az ad app show --id fee96351-9eda-4fb7-a91b-ac46e9c07358 --query objectId --output tsv)
echo $id
az rest --method PATCH --url https://graph.microsoft.com/v1.0/applications/$id --body "{'api':{'requestedAccessTokenVersion':2}}"
```

MANUAL: copy token in line below to test

```bash
TOKEN="<snip>"
curl -H "Authorization: Bearer ${TOKEN}" https://thx1140back.azurewebsites.net
```

MANUAL: admin consent on backend, so that it can get an access token for graph

MANUAL: create secret on backend app registration

MANUAL: update `back/index.php` with secret

## Sample on behalf of 
```bash
response=$(curl -X POST -F 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer' \
             -F 'client_id=fee96351-9eda-4fb7-a91b-ac46e9c07358' \
             -F 'client_secret=<snip>' \
             -F "assertion=${TOKEN}" \
             -F "scope=openid profile email" \
             -F "requested_token_use=on_behalf_of" \
        https://login.microsoftonline.com/thx1139corp.onmicrosoft.com/oauth2/v2.0/token|jq -r ".access_token")

curl -H "Authorization: Bearer ${response}" https://graph.microsoft.com/oidc/userinfo
```

## Links

<https://stackoverflow.com/questions/55282008/is-it-possible-to-add-multiple-audiences-to-azureadbearer-token>

<https://www.ludovicmedard.com/azure-api-management-and-oauth-tokens-for-multiple-backend-services/>

behind reverse proxy

```bash
az rest --uri /subscriptions/5053b074-62e4-469e-91a2-f56553bdfebb/resourceGroups/rg-appsvc/providers/Microsoft.Web/sites/app1thx1139/config/authsettingsV2?api-version=2020-09-01 --method get

az rest --uri /subscriptions/5053b074-62e4-469e-91a2-f56553bdfebb/resourceGroups/rg-appsvc/providers/Microsoft.Web/sites/app1thx1139/config/authsettingsV2?api-version=2020-09-01 --method get > auth.json

az rest --uri /subscriptions/5053b074-62e4-469e-91a2-f56553bdfebb/resourceGroups/rg-appsvc/providers/Microsoft.Web/sites/app1thx1139/config/authsettingsV2?api-version=2020-09-01 --method put --body @auth.json 
```

## More links

<https://azure.github.io/AppService/2021/03/26/Secure-resilient-site-with-custom-domain.html>

Example call back links and token store

<https://thx1140front.azurewebsites.net/.auth/login/aad/callback>

<https://thx1140front.azurewebsites.net/.auth/me>

<https://docs.microsoft.com/en-us/azure/active-directory/develop/howto-add-app-roles-in-azure-ad-apps>

<https://docs.microsoft.com/en-us/azure/app-service/tutorial-auth-aad?pivots=platform-linux>

<https://auth0.com/docs/secure/tokens/access-tokens>

ID Tokens should not be used to gain access to an API. Each token contains information for the intended audience (which is usually the recipient). Per the OpenID Connect specification, the audience of the ID Token (indicated by the aud claim) must be the client ID of the application making the authentication request. If this is not the case, you should not trust the token. Conversely, an API expects a token with the aud value to equal the API's unique identifier. Therefore, unless you maintain control over both the application and the API, sending an ID Token to an API will generally not work. Furthermore, the ID Token is signed with a secret known only to the application itself. If an API were to accept an ID Token, it would have no way of knowing if the application has modified the token (such as adding more scopes) and resigned it.

## More notes

```bash
api://<back app client id>/user_impersonation
```

Tool tip: Allow the application to access thx1140back on behalf of the signed-in user
