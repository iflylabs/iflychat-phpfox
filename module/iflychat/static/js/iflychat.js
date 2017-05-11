var iflychat_bundle = document.createElement("SCRIPT");
iflychat_bundle.src = "//"+Drupal.settings.drupalchat.iflychat_external_cdn_host+"/js/iflychat-v2.min.js?app_id="+Drupal.settings.drupalchat.iflychat_app_id;
iflychat_bundle.async="async";
iflychat_app_id = Drupal.settings.drupalchat.iflychat_app_id;
iflychat_auth_token = Drupal.settings.drupalchat.iflychat_auth_token;
iflychat_auth_url = Drupal.settings.drupalchat.iflychat_auth_url;
document.body.appendChild(iflychat_bundle);