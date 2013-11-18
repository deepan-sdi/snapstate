/*	Front End Validation Scripts	*/

/*	Global Vars
 	Var: Email Regex
*/
var emailRegex	= /^[_A-Za-z0-9-]+(\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,4})$/;
var alphanumeric_alteast	= /^.*[a-zA-Z\u00C0-\u017F0-9]+/;
var alphanumeric_mustbe		= /^[a-zA-Z0-9]+$/;
var alpha_mustbe			= /^[a-zA-Z\u00C0-\u017F]+$/;

/*	Function: validateRegistrationForm
 	Form: Registration
*/
	function validateRegistrationForm() {
		var error_flag						= 0,
			msg								= '',
			form							= $('#registration_form'),
			first_name						= $('#first_name'),
			last_name						= $('#last_name'),
			email_address					= $('#email_address'),
			registration_password			= $('#registration_password'),
			registration_confirm_password	= $('#registration_confirm_password');
		
		$('.error_msg').remove();
		$('#signup').addClass('ind-pop-h2');
		$('#signup').removeClass('ind-pop-h2_new');
		
		first_name.removeClass('error_field');
		last_name.removeClass('error_field');
		email_address.removeClass('error_field');
		registration_password.removeClass('error_field');
		registration_confirm_password.removeClass('error_field');
		
		if(first_name.val() == '') {	// First Name
			msg	= 'Enter your First Name';
			first_name.addClass('error_field');
			first_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(first_name.val().length < 3) {
			msg	= 'First Name must be atleast 3 characters';
			first_name.addClass('error_field');
			first_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alpha_mustbe.test(first_name.val())) {
			msg	= 'First Name contains invalid character';
			first_name.addClass('error_field');
			first_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(last_name.val() == '') {	// Last Name
			msg	= 'Enter your Last Name';
			last_name.addClass('error_field');
			last_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(last_name.val().length < 3) {
			msg	= 'Last Name must be atleast 3 characters';
			last_name.addClass('error_field');
			last_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alpha_mustbe.test(last_name.val())) {
			msg	= 'Last Name contains invalid character';
			last_name.addClass('error_field');
			last_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(email_address.val() == '') {	// Email Address
			msg	= 'Enter your Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!emailRegex.test(email_address.val())) {
			msg	= 'Enter a valid Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(registration_password.val() == '' || registration_password.val().length < 6) {	// Password
			msg	= 'Password must be atleast 6 characters';
			registration_password.addClass('error_field');
			registration_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alphanumeric_alteast.test(registration_password.val())) {
			msg	= 'Password must contain atleast 1 alphanumeric character';
			registration_password.addClass('error_field');
			registration_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(registration_confirm_password.val() == '') {	// Confirm Password
			msg	= 'Password must be confirmed';
			registration_confirm_password.addClass('error_field');
			registration_confirm_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(registration_confirm_password.val() != registration_password.val()) {
			msg	= 'Passwords do not match';
			registration_confirm_password.addClass('error_field');
			registration_confirm_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			$('#signup').addClass('ind-pop-h2_new');
			$('#signup').removeClass('ind-pop-h2');
			return false;
		} else {
			showOverlay();
			$.post('/front/index/validate-registration', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == -1) {	// Email is already exist
					msg	= 'Email Address is already registered';
					email_address.addClass('error_field');
					email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
					$('#signup').addClass('ind-pop-h2_new');
					$('#signup').removeClass('ind-pop-h2');
				} else if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					$('#registration_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
					$('#signup').addClass('ind-pop-h2_new');
					$('#signup').removeClass('ind-pop-h2');
				} else if(data != '') {
					resetRegistrationForm();
					$('#signup').modal('hide');
					$('#message_content').html('Thank you for registering!  A confirmation email has been sent to your email address. Please click on the link in that email in order to activate your account.');
					$('#confirmation_modal').click();
				}
			});
			return false;
		}
	}
	
/*	Function: resetRegistrationForm
	Form: Registration
*/
	function resetRegistrationForm() {
		var first_name						= $('#first_name'),
			last_name						= $('#last_name'),
			email_address					= $('#email_address'),
			registration_password			= $('#registration_password'),
			registration_confirm_password	= $('#registration_confirm_password');
		
		$('.error_msg').remove();
		$('#signup').addClass('ind-pop-h2');
		$('#signup').removeClass('ind-pop-h2_new');
			
		first_name.removeClass('error_field');
		last_name.removeClass('error_field');
		email_address.removeClass('error_field');
		registration_password.removeClass('error_field');
		registration_confirm_password.removeClass('error_field');
		first_name.val('');
		last_name.val('');
		email_address.val('');
		registration_password.val('');
		registration_confirm_password.val('');
	}

/*	Function: showOverlay
	Page: Common
*/
	function showOverlay() {
		$('.progress-indicator').css('display', 'block');
		//$('body').css('overflow', 'hidden');
	}

/*	Function: hideOverlay
	Page: Common
*/
	function hideOverlay() {
		$('.progress-indicator').css('display', 'none');
		//$('body').css('overflow', 'auto');
	}

/*	Function: scrolltotop
	Page: Common
*/
	function scrolltotop() {
		window.scroll(0, 0);
	}
	
/*	Function: validateLoginForm
	Form: Login
*/
	function validateLoginForm() {
		var error_flag			= 0,
			msg					= '',
			form				= $('#login_form'),
			email_address		= $('#login_email'),
			password			= $('#login_password');
		
		$('.error_msg').remove();
		$('#login').addClass('ind-pop-h');
		$('#login').removeClass('ind-pop-h_new');
		
		email_address.removeClass('error_field');
		password.removeClass('error_field');
		
		if(email_address.val() == '') {	// Email Address
			msg	= 'Enter your Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!emailRegex.test(email_address.val())) {
			msg	= 'Enter a valid Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(password.val() == '' || password.val().length < 6) {	// Password
			msg	= 'Password must be atleast 6 characters';
			password.addClass('error_field');
			password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			$('#login').addClass('ind-pop-h_new');
			$('#login').removeClass('ind-pop-h');
			return false;
		} else {
			showOverlay();
			$.post('/front/index/validate-login', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == -1) {	// Email is already exist
					msg	= 'Incorrect Email Address or Password';
					email_address.addClass('error_field');
					email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
					password.addClass('error_field');
					$('#login').addClass('ind-pop-h_new');
					$('#login').removeClass('ind-pop-h');
				} else if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					$('#login_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
					$('#login').addClass('ind-pop-h_new');
					$('#login').removeClass('ind-pop-h');
				} else if(data != '') {
					resetLoginForm();
					$('#login').modal('hide');
					showOverlay();
					window.location.href	= '/';
				}
			});
			return false;
		}
	}

/*	Function: resetRegistrationForm
	Form: Registration
*/
	function resetLoginForm() {
		var email_address	= $('#login_email'),
			password		= $('#login_password');
		
		$('.error_msg').remove();
		$('#login').addClass('ind-pop-h');
		$('#login').removeClass('ind-pop-h_new');
		
		email_address.removeClass('error_field');
		password.removeClass('error_field');
		email_address.val('');
		password.val('');
	}
	
/*	Function: resetForgetPasswordForm
	Form: ForgetPassword
*/
	function resetForgetPasswordForm() {
		var email_address	= $('#forget_password_email');
		
		$('.error_msg').remove();
		$('#forget-password').addClass('ind-pop-h4');
		$('#forget-password').removeClass('ind-pop-h4_new');
		
		email_address.removeClass('error_field');
		email_address.val('');
	}

/*	Function: validateForgerPasswordForm
	Form: ForgetPassword
*/
	function validateForgerPasswordForm() {
		var error_flag			= 0,
			msg					= '',
			form				= $('#forget_password_form'),
			email_address		= $('#forget_password_email');
		
		$('.error_msg').remove();
		$('#forget-password').addClass('ind-pop-h4');
		$('#forget-password').removeClass('ind-pop-h4_new');
		
		email_address.removeClass('error_field');
		
		if(email_address.val() == '') {	// Email Address
			msg	= 'Enter your Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!emailRegex.test(email_address.val())) {
			msg	= 'Enter a valid Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			$('#forget-password').addClass('ind-pop-h4_new');
			$('#forget-password').removeClass('ind-pop-h4');
			return false;
		} else {
			showOverlay();
			$.post('/front/index/validate-forget-password', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == -1) {	// Email does not exist.
					msg	= 'Email Address does not exist';
					email_address.addClass('error_field');
					email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
					$('#forget-password').addClass('ind-pop-h4_new');
					$('#forget-password').removeClass('ind-pop-h4');
				} else if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					$('#forget-password_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
					$('#forget-password').addClass('ind-pop-h4_new');
					$('#forget-password').removeClass('ind-pop-h4');
				} else if(data != '') {
					resetForgetPasswordForm();
					$('#forget-password').modal('hide');
					$('#message_content').html('We have sent an email with instructions to login. Your existing password has been changed.');
					$('#confirmation_modal').click();
				}
			});
			return false;
		}
	}

/*	Function: fetchFBUserInfo
	Form: Login with Facebook
*/
	function fetchFBUserInfo() {
		FB.api('/me', function(response) {
			if(response.email != undefined && response.first_name != undefined && response.last_name != undefined) {
				var user_email		= response.email,
					user_firstname	= response.first_name,
					user_lastname	= response.last_name,
					user_fbuid		= response.id;
					
					$.post('/front/index/validate-fblogin', {user_fbuid: user_fbuid, user_email: user_email, user_firstname: user_firstname, user_lastname: user_lastname}, function(data){
						if($.trim(data) == 1) {
							window.location.reload();
						} else {
							alert('Authentication Failed. Please try again.');
						}
					});
			} else {
				console.log("Error!");
			}
		});
	}

/*	Function: fetchFriendsInfo
	Form: Login with Facebook
*/
	function fetchFriendsInfo() {
		FB.api('/me/friends', function(response) {
			if(response.data) {
				var friends	= response.data;
				for(x in friends) {
					console.log(friends[x].name+'-'+friends[x].id);
				}
			} else {
				console.log("Error!");
			}
		});
	}

/*	Function: fbLogin
	Form: Login with Facebook
*/
	function fblogin() {
		FB.login(function(response) {
			fetchFBUserInfo();
			fetchFriendsInfo();
		}, {scope: 'email,user_likes,read_stream,publish_stream'});
	}

/*	Function: loginWithFB
	Form: Login with Facebook
*/
	/*	function loginWithFB(appID, returnURL, protocolName) {
		location.href	= protocolName+"://www.facebook.com/dialog/oauth/?client_id="+appID+"&redirect_uri="+returnURL+"&scope=email,user_activities,user_likes,publish_actions,read_stream,publish_stream";
	}	*/