
<md-content ng-cloak layout="column" ng-controller="quesController">
    <div layout="row" layout-sm="column" layout-align="space-around" ng-if="opening">
        <md-progress-circular md-mode="indeterminate"></md-progress-circular>
    </div>
    <md-content layout="column" layout-padding layout-align="start center">
        <div style="width:960px">
            <survey-book ng-if="book" book="book"></survey-book>
        </div>
    </md-content>
</md-content>

<script type="text/ng-template" id="list">
    @include('files.survey.template_question_list')
</script>
<script type="text/ng-template" id="checkbox">
    @include('files.survey.template_question_checkbox')
</script>
<script type="text/ng-template" id="select">
    @include('files.survey.template_question_select')
</script>
<script type="text/ng-template" id="radio">
    @include('files.survey.template_question_radio')
</script>
<script type="text/ng-template" id="scale">
    @include('files.survey.template_question_scale')
</script>
<script type="text/ng-template" id="text">
    @include('files.survey.template_question_text')
</script>
<script type="text/ng-template" id="number">
    @include('files.survey.template_question_number')
</script>

<script src="/js/ng/ngSurvey.js"></script>

<script>
app.requires.push('ngSurvey');
app.controller('quesController', function quesController($scope, $http, $filter, surveyFactory){

    $scope.getBook = function() {
        $scope.$parent.main.loading = true;
        $http({method: 'POST', url: 'getBook', data:{}})
        .success(function(data, status, headers, config) {
            console.log(data);
            $scope.book = data.book;
            $scope.$parent.main.loading = false;
        }).error(function(e) {
            console.log(e);
        });
    };

    $scope.getBook();

});
</script>
