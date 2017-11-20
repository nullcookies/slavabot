/**
 * Cube - Bootstrap Admin Theme
 * Copyright 2014 Phoonio
 */

var app = angular.module('cubeWebApp', [
	'ngRoute',
	'angular-loading-bar',
	'ngAnimate',
	'easypiechart',
    'angularMoment',
    'ui.utils'
]);

app.config(['cfpLoadingBarProvider', function(cfpLoadingBarProvider) {
	cfpLoadingBarProvider.includeBar = true;
	cfpLoadingBarProvider.includeSpinner = true;
	cfpLoadingBarProvider.latencyThreshold = 100;
}]);

/**
 * Configure the Routes
 */

app.config(['$routeProvider', function ($routeProvider) {
	$routeProvider
		.when("/", {
			redirectTo:'/dasboard'
		})
        .when("/potential", {
            templateUrl: "views/users.html",
            controller: "potentialCtrl",
            title: 'Потенциальные клиенты',
        })
		.when("/potential/:id", {
			templateUrl: "views/filter.html",
			controller:'filterCtrl',
            title: 'Сохраненный фильтр'
		})
        .when("/potential/detail/:id", {
            templateUrl: "views/detail.html",
            controller:'detailCtrl',
            title: 'Детальный просмотр'
        })
        .when("/pages/contacts", {
            templateUrl: "views/contacts.html",
            controller: "contactsCtrl",
            title: 'Купленные контакты',
        })
		.when("/dasboard", {
			templateUrl: "views/dashboard.html",
			controller: "dashboardCtrl",
			title: 'Главная'
		})
        .when("/pages/config", {
            templateUrl: "/site/config/",
            controller: "configCtrl",
            title: 'Настройки'
        })
        .when("/pages/help", {
            templateUrl: "views/help.html",
            controller: "helpCtrl",
            title: 'Помощь'
        })
        .when("/error/owner", {
            templateUrl: "views/errors/owner.html",
            title: 'Ошибка'
        })
        .when("/pages/social", {
            templateUrl: "/social",
            controller: "socialCtrl",
            title: 'Соц.сети'
        })
        .when("/history", {
            templateUrl: "/history",
            controller: "historyCtrl",
            title: 'История'
        })
        .when("/history/responses", {
            templateUrl: "/responses",
            controller: "responsesCtrl",
            title: 'Ответы'
        })
        .when("/history/posts", {
            templateUrl: "/posts",
            controller: "postsCtrl",
            title: 'Посты и комментарии'
        })
		.otherwise({
			redirectTo:'/error-404'
		});
}]);

app.run(['$location', '$rootScope', function($location, $rootScope) {
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
        $rootScope.title = current.$$route.title;
    });
}]);