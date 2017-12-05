<?
use frontend\controllers\SocialController;
use common\models\User;
?>

<div class="row">
    <!--<div class="col-lg-3 col-sm-6 col-xs-12">-->
    <!--<div class="main-box infographic-box colored emerald-bg">-->
    <!--<i class="fa fa-file-code-o"></i>-->
    <!--<span class="headline">В файле</span>-->
    <!--<span ng-bind="webhooks" class="value"></span>-->
    <!--</div>-->
    <!--</div>-->

    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="main-box infographic-box colored green-bg">
            <i class="fa fa-database "></i>
            <span class="headline">В базе</span>
            <span class="value" ng-bind="indb"></span>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6 col-xs-12">
        <div class="main-box infographic-box colored red-bg">
            <i class="fa fa-user"></i>
            <span class="headline">Ссылок на профили</span>
            <span class="value" ng-bind="norm"></span>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-md-12">
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2 class="pull-left">Потенциальные клиенты</h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="row">
                    <div class="col-md-9">
                        <div id="graph-bar" style="height: 240px; padding: 0px; position: relative;"></div>
                    </div>
                    <div class="col-md-3">
                        <ul class="graph-stats">
                            <li>
                                <div class="clearfix">
                                    <div class="title pull-left">
                                        Вконтакте
                                    </div>
                                    <div class="value pull-right" title="{{( vk/indb )*100}}%" data-toggle="tooltip">
                                        {{vk}}
                                    </div>
                                </div>
                                <div class="progress">
                                    <div style="width: {{( vk/indb )*100}}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{( vk/indb )*100}}" role="progressbar" class="progress-bar">
                                        <span class="sr-only">{{( vk/indb )*100}}%</span>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="clearfix">
                                    <div class="title pull-left">
                                        Facebook
                                    </div>
                                    <div class="value pull-right" title="{{( fb/indb )*100}}%" data-toggle="tooltip">
                                        {{fb}}
                                    </div>
                                </div>
                                <div class="progress">
                                    <div style="width: {{( fb/indb )*100}}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{( fb/indb )*100}}" role="progressbar" class="progress-bar">
                                        <span class="sr-only">{{( fb/indb )*100}}%</span>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="clearfix">
                                    <div class="title pull-left">
                                        Одноклассники
                                    </div>
                                    <div class="value pull-right" title="{{( ok/indb )*100}}%" data-toggle="tooltip">
                                        {{ok}}
                                    </div>
                                </div>
                                <div class="progress">
                                    <div style="width: {{( ok/indb )*100}}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{( ok/indb )*100}}" role="progressbar" class="progress-bar">
                                        <span class="sr-only">{{( ok/indb )*100}}%</span>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="clearfix">
                                    <div class="title pull-left">
                                        Twitter
                                    </div>
                                    <div class="value pull-right" title="{{( twitter/indb )*100}}%" data-toggle="tooltip">
                                        {{twitter}}
                                    </div>
                                </div>
                                <div class="progress">
                                    <div style="width: {{( twitter/indb )*100}}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{( twitter/indb )*100}}" role="progressbar" class="progress-bar">
                                        <span class="sr-only">{{( twitter/indb )*100}}%</span>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="clearfix">
                                    <div class="title pull-left">
                                        Instagram
                                    </div>
                                    <div class="value pull-right" title="{{( inst/indb )*100}}%" data-toggle="tooltip">
                                        {{inst}}
                                    </div>
                                </div>
                                <div class="progress">
                                    <div style="width: {{( inst/indb )*100}}%;" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{( inst/indb )*100}}" role="progressbar" class="progress-bar">
                                        <span class="sr-only">{{( inst/indb )*100}}%</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?if(Yii::$app->user->identity['authorized']==0){?>

    <div class="wizard" id="satellite-wizard" data-title="Мастер настройки">

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
                    <button type="button" class="btn btn-success" ng-click="VkSave()">Добавить аккаунт</button>
                </div>
            </div>
            <div class="vkcard__content" ng-show="vkGroupBox">
                <h4 class="modal-title" id="myModalLabel">
                    Укажите активное сообщество для аккаунта: {{unprocessedName}}
                </h4>
                <div class="form-group">
                    <div class="radio" style="margin-top: 25px;" ng-repeat="groupe in unprocessed">
                        <input type="radio" name="optionsRadios" ng-model="userSelection.activeValue" id="optionsRadios{{groupe.id}}" ng-value="groupe">
                        <label for="optionsRadios{{groupe.id}}">
                            <img src="{{groupe.photo_50}}" alt="" style="margin-top: -15px; border-radius: 100%;">
                            <span style="margin-left: 10px; display: block; float: right;">{{groupe.name}}</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-success" ng-click="accountSave('vk')">Сохранить</button>
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
                    <button type="button" class="btn btn-success" ng-click="InstaSave()">Сохранить</button>
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
                        'http://'.$_SERVER['SERVER_NAME'].'/social/wizard-fb',
                        'Привязать аккаунт Facebook',
                        'facebook',
                        '<a class="btn btn-success" href="LINK" target="_blank" id="ID">TEXT</a>'
                    );
                ?>
            </div>
            <div class="fbcard__content" ng-show="fbGroupBox">
                <h4 class="modal-title" id="myModalLabel">
                    Укажите активное сообщество для аккаунта: {{unprocessedName}}
                </h4>
                <div class="form-group">
                    <div class="radio" style="margin-top: 25px;" ng-repeat="groupe in unprocessed">
                        <input type="radio" name="optionsRadios" ng-model="userSelection.activeValue" id="optionsRadios{{groupe.id}}" ng-value="groupe">
                        <label for="optionsRadios{{groupe.id}}">
                            <img src="{{groupe.photo_50}}" alt="" style="margin-top: -15px; border-radius: 100%;">
                            <span style="margin-left: 10px; display: block; float: right;">{{groupe.name}}</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-success" ng-click="accountSave('fb')">Сохранить</button>
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
            <p>Подключитесь к <a href="http://t.me/MltTempBot" target="_blank">Славаботу</a> в Telegram.

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
        $(document).ready(function() {

            var options = {
                contentHeight : 600,
                contentWidth : 1000,
                backdrop: 'static',
                //show: true,
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

            wizard.show();
        });
    </script>
<?
    User::setAuth(Yii::$app->user->identity['id']);
}
?>
