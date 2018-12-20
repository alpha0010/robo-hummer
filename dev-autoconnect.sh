#!/bin/bash
# Use the SSO public key of the site running on this server.
# Trust the UUIDs of sso client sites.

# TODO: Use better way to find out which site is sso
# (DB_PACKAGE=sso in the .env)
function find-sso-site {
	dart p | grep sso | sed 's/ \+/\t/g' | cut -f3 | head -n 1
}

SSO_DIR=$(find-sso-site)

if [[ -n "$SSO_DIR" ]]; then
	cat "$SSO_DIR/htdocs/api/storage/oauth-public.key" | xargs -0 dart +artisan robo:set-sso-key --

	pushd "$SSO_DIR" > /dev/null
	# These are tab separated so that the `cut` command below can grab the correct fields.
	CLIENT_SITES=$(echo 'echo "id\tuuid                                 \tbase\n"; \
		foreach( App\Models\Website::all() as $site ){ \
			echo $site->id; echo "\t"; echo $site->user->uuid; echo "\t"; echo $site->base; echo "\n"; \
		}' | dart +artisan tinker -q)
	popd > /dev/null

	echo "$CLIENT_SITES";

	read -p "Enter space separated list of ids of the sites you would like to trust: " totrust
	for id in $totrust; do
		UUID=$(echo "$CLIENT_SITES" | grep "^$id" | cut -f2)
		if [[ -n "$UUID" ]]; then
			dart +artisan robo:trust-uuid "$UUID"
		else
			echo "The site with the id '$id' was not found."
		fi
	done

else
	echo "No SSO Site was found, autoconnection failed."
fi
