<?
use frontend\controllers\SocialController;
use common\models\User;
?>

    <div class="row">
        <div class="col-lg-12">
            <ol class="breadcrumb">
                <li><a href="">Главная</a></li>
                <li class="active"><span>История</span></li>
            </ol>
            <br>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="main-box clearfix">
                <header class="main-box-header clearfix">

                </header>
                <div class="main-box-body clearfix">
                    <div class="tabs-wrapper">
                        <ul class="nav nav-tabs">
                            <li ng-class="{'active' : allPlane}"><a ng-click="setAll()">Все посты</a></li>
                            <li ng-class="{'active' : plannedPlane}"><a ng-click="setPlanned()">Запланированные посты</a></li>
                        </ul>
                        <div class="tab-content">
                            <div ng-class="{'active in' : allPlane}" class="tab-pane fade" id="tab-all">
                                <div class="table-responsive" id="top">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th class="text-center"><a href="" ng-click="changeOrder()" class="{{order}}"><span>Дата публикации</span></a></th>
                                            <th class="text-center"><span>Содержимое поста</span></th>
                                            <th class="text-center"><span>Превью</span></th>
                                            <th class="text-center"><span>Аккаунты</span></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr ng-repeat="element in history" ng-include='"views/common/historyTable.html"'></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div ng-include='"views/common/historyPagination.html"'></div>
                            </div>
                            <div ng-class="{'active in' : plannedPlane}" class="tab-pane fade" id="tab-planned">
                                <div class="table-responsive" id="topPlanned">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th class="text-center"><a href="" ng-click="changeOrder()" class="{{order}}"><span>Дата публикации</span></a></th>
                                            <th class="text-center"><span>Содержимое поста</span></th>
                                            <th class="text-center"><span>Превью</span></th>
                                            <th class="text-center"><span>Аккаунты</span></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr ng-repeat="element in planned" ng-include='"views/common/historyTable.html"'></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div ng-include='"views/common/historyPlannedPagination.html"'></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?if(Yii::$app->user->identity['authorized']==0){?>
    <div class="wizard" id="satellite-wizard" ng-controller='dashboardCtrl' data-title="Мастер настройки">

        <!-- Step 1 Name & FQDN -->
        <div class="wizard-card" data-cardname="name">
            <h3>Добро пожаловать!</h3>
            <div class="wizard-input-section">
                Выполните все шаги этого мастера  <br>
                для настройки работы Славабота
            </div>
        </div>
        <div class="wizard-card" data-cardname="vkcard">
            <h3>ВКонтакте</h3>
            <div class="vkcard__content" ng-show="vkAuthBox">
                <div class="alert alert-danger" ng-show="vkError">
                    <i class="fa fa-times-circle fa-fw fa-lg"></i>
                    <strong>Ошибка!</strong> {{vkErrorText}}
                </div>
                <div class="form-group" ng-class="{'has-error' : vkError || vkLoginError}">
                    <label>Логин (Номер телефона)</label>
                    <input type="text" class="form-control" ng-model="vkLogin" ui-mask="9 (999) 999-99-99" required>
                </div>
                <div class="form-group" ng-class="{'has-error' : vkError || vkPasswordError}">
                    <label>Пароль</label>
                    <input type="password" class="form-control"  ng-model="vkPassword"  placeholder="Пароль">
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-success" ng-click="VkSave()">Подключить аккаунт</button>
                </div>
            </div>
            <div class="vkcard__content" ng-show="vkGroupBox">
                <h4 class="modal-title" id="myModalLabel">
                    Укажите активное сообщество для аккаунта: {{unprocessedName}}
                </h4>
                <div class="form-group scroll-block">
                    <div class="radio" style="margin-top: 25px;" ng-repeat="groupe in unprocessed">
                        <input type="radio" name="optionsRadios" ng-model="userSelection.activeValueVK" value="{{groupe.id}}" id="optionsRadios{{groupe.id}}">
                        <label for="optionsRadios{{groupe.id}}">
                            <img src="{{groupe.photo_50}}" alt="" style="margin-top: -15px; border-radius: 100%;">
                            <span style="margin-left: 10px; display: block; float: right;">{{groupe.name}}</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" ng-class="{'active_save_btn' : vkGroupBox}" class="btn btn-success" ng-click="accountSave('vk')">Сохранить</button>
                </div>
            </div>
            <div class="vkcard__content" ng-show="vkfinish">
                <div class="alert alert-success">
                    <i class="fa fa-check-circle fa-fw fa-lg"></i>
                    <strong>Аккаунт привязан!</strong> Привязка аккаунта Вконтакте успешно завершена!
                </div>
            </div>
        </div>
        <div class="wizard-card" data-cardname="instacard">
            <h3>Instagram</h3>
            <div class="instacard__content" ng-show="instaAuthBox">
                <div class="alert alert-danger" ng-show="instaError.length>3">
                    <i class="fa fa-times-circle fa-fw fa-lg"></i>
                    <span ng-bind-html="sce.trustAsHtml(instaError)"></span>
                </div>
                <div class="form-group">
                    <label>Логин</label>
                    <input type="text" class="form-control" ng-model="InstaLogin" placeholder="Имя пользователя">
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" class="form-control" ng-model="InstaPassword" placeholder="Пароль">
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-success" ng-click="InstaSave()">Подключить аккаунт</button>
                </div>
            </div>
            <div class="vkcard__content" ng-show="instafinish">
                <div class="alert alert-success">
                    <i class="fa fa-check-circle fa-fw fa-lg"></i>
                    <strong>Аккаунт привязан!</strong> Привязка аккаунта Instagram успешно завершена!
                </div>
            </div>
        </div>
        <div class="wizard-card" data-cardname="fbcard">
            <h3>Facebook</h3>
            <div class="fbcard__auth" ng-show="fbAuthBox">
                <?=
                    SocialController::getFBBtn(
                        'https://'.$_SERVER['HTTP_HOST'].'/social/wizard-fb',
                        'Подключить аккаунт Facebook',
                        'facebook',
                        '<a class="btn btn-success" href="LINK" target="_blank" id="ID">TEXT</a>'
                    );
                ?>
            </div>
            <div class="fbcard__content" ng-show="fbGroupBox">
                <h4 class="modal-title" id="myModalLabel">
                    Укажите активное сообщество для аккаунта: {{unprocessedName}}
                </h4>
                <div class="form-group scroll-block">
                    <div class="radio" style="margin-top: 25px;" ng-repeat="groupe in unprocessed">
                        <input type="radio" name="optionsRadios" ng-model="userSelection.activeValueFB" value="{{groupe.id}}" id="optionsRadios{{groupe.id}}">
                        <label for="optionsRadios{{groupe.id}}">
                            <img src="{{groupe.photo_50}}" alt="" style="margin-top: -15px; border-radius: 100%;">
                            <span style="margin-left: 10px; display: block; float: right;">{{groupe.name}}</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" ng-class="{'active_save_btn' : fbGroupBox}" class="btn btn-success" ng-click="accountSave('fb')">Сохранить</button>
                </div>
            </div>
            <div class="fbcard__content" ng-show="fbFinish">
                <div class="alert alert-success">
                    <i class="fa fa-check-circle fa-fw fa-lg"></i>
                    <strong>Аккаунт привязан!</strong> Привязка аккаунта Facebook успешно завершена!
                </div>
            </div>
        </div>
        <div class="wizard-card" data-cardname="tlgcard">
            <h3>Подключение Telegram</h3>
            <p>Подключитесь к <a href="<?=\common\services\StaticConfig::botUrl()?>" target="_blank">Славаботу</a> в Telegram.

            </p>
        </div>
        <div class="wizard-card" data-cardname="lastcard">
            <h3>Настройка закончена</h3>
            <p>
                Славабот готов к работе. <br>
                Все указанные настройки в дальнейшем вы можете изменить в соответствующих разделах личного кабинета.
            </p>
        </div>

    </div>

    <script type="text/javascript">

        function setCookie(name, value, options) {
            options = options || {};

            var expires = options.expires;

            if (typeof expires == "number" && expires) {
                var d = new Date();
                d.setTime(d.getTime() + expires * 1000);
                expires = options.expires = d;
            }
            if (expires && expires.toUTCString) {
                options.expires = expires.toUTCString();
            }

            value = encodeURIComponent(value);

            var updatedCookie = name + "=" + value;

            for (var propName in options) {
                updatedCookie += "; " + propName;
                var propValue = options[propName];
                if (propValue !== true) {
                    updatedCookie += "=" + propValue;
                }
            }

            document.cookie = updatedCookie;
        }

        function getCookie(name) {
            var matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));
            return matches ? decodeURIComponent(matches[1]) : false;
        }

        $(document).ready(function() {

            var options = {
                contentHeight : 450,
                contentWidth : 750,
                backdrop: 'static',
                buttons: {
                    cancelText: "Отмена",
                    nextText: "Далее",
                    backText: "Назад",
                    submitText: "Начать работу"
                }
            };

            var wizard = $("#satellite-wizard").wizard(options);

            wizard.on('closed', function() {
                wizard.reset();
            });

            wizard.on("submit", function(wizard) {
                wizard.close();
            });

            $(".wizard-next").on('click', function(){
                $('.active_save_btn').click();
            });
            if(!getCookie('modal')){
                wizard.show();
                setCookie('modal', true)
            }
        });
    </script>
    <?
    User::setAuth(Yii::$app->user->identity['id']);
}
?>