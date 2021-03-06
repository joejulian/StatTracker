var StatTracker = new function() {
	this.baseUrl = "http://api.johnluetke.net/ingress/stats/";
	this.pageToLoad = "dashboard";
	this.message = null;

	this.hideLogout = function() {
		$("#gDisconnect").hide();
	};

	this.loadPage = function() {
		$(".tabs li[class~='" + this.pageToLoad +"'] a").addClass("selected");
		$.ajax({url: this.baseUrl + "page/" + this.pageToLoad,
			type: "GET",
			dataType: "html",
			statusCode: {
				500: function() {
					alert("Internal Server Error");
				}
			},
			success: function(html) {
				$("#main-content").html(html);
				onPageLoad();
			}
		});
	}

	this.showLoginDialog = function() {
		$("#login-dialog").dialog("open");
	}

	this.showLoading = function(selector) {
		$(selector).html("<img src='./resources/images/ADA.gif' />");
	}

	this.authenticate = function() {
		$.ajax({url: StatTracker.baseUrl + "authenticate?action=login",
			type: 'GET',
			contentType: 'json',
			success: function(result) {
				if (typeof result !== "object")
					result = JSON.parse(result);
				if (result.status == "authentication_required") {
					modal = $("#login-dialog").dialog("isOpen");
					if (!modal) $("#login-dialog").dialog("open");
					$("#login-buttons .google a").attr("href", result.url);
				}
				else if (result.status == "registration_required") {
					$("#login-buttons").hide();
					$("#login-dialog").dialog("open");
					message = "An email has been sent to ";
					message += "<strong>" + result.email + "</strong>";
					message += " with additional steps for registering.";
					$("#login-message").html(message);
				}
				else if (result.status == "okay") {
					modal = $("#login-dialog").dialog("isOpen");
					if (modal) $("#login-dialog").dialog("close");
					if (result.agent.numSubmissions == 0) {
						StatTracker.message = "Stats must be submitted before this tool can be utilized";
						StatTracker.pageToLoad = "submit-stats";
					}

					StatTracker.loadPage();
			}
			},
			processData: false
		});

	}

	this.signinCallback = function(authResult) {
		this.hideLogout();
		if (authResult['access_token']) {
			$("#gConnect").hide();
			this.processSignin(authResult);
		}
		else if (authResult['error']) {
			if (authResult['error'] == "immediate_failed" ||
			    authResult['error'] == "user_signed_out") {
				this.showLoginDialog();
			}
			else {
				console.log("AUTH ERROR " + authResult['error']);
			}
		}
	};

	this.processSignin = function(authResult) {
		$.ajax({url: StatTracker.baseUrl + "authenticate?action=login",
			type: 'POST',
			data: authResult.code,
			contentType: 'application/octet-stream; charset=utf-8',
			success: function(result) {
				if (typeof result !== "object")
					result = JSON.parse(result);
				if (result.status == "registration_required") {
					$("#login-dialog").dialog("open");
					message = "An email has been sent to ";
					message += result.email;
					message += " with additional steps for registering.";
					$("#login-message").html(message);
				}
				else if (result.status == "okay") {
					modal = $("#login-dialog").dialog("isOpen");
					if (modal) $("#login-dialog").dialog("close");
					if (result.agent.numSubmissions == 0) {
						StatTracker.message = "Stats must be submitted before this tool can be utilized";
						StatTracker.pageToLoad = "submit-stats";
					}

					StatTracker.loadPage();
			}
			},
			processData: false
		});
	}
};
