
<md-content ng-cloak layout="column" ng-controller="quesController">

    <div layout="row" layout-sm="column" layout-align="space-around" ng-if="opening">
        <md-progress-circular md-mode="indeterminate"></md-progress-circular>
    </div>


        <md-content layout="column" layout-padding layout-align="start center">
            <div style="width:960px">
                <div ng-if="question" question="question" branchs="branchs" childrens="childrens"></div>
            </div>
            <md-button class="md-raised md-primary" ng-click="next()">繼續</md-button>
        </md-content>

</md-content>

<script type="text/ng-template" id="explain">
    @include('files.survey.template_question_explain')
</script>
<!--<script type="text/ng-template" id="radio">
    @include('files.survey.template_question_radio')
</script>-->
<script type="text/ng-template" id="select">
    @include('files.survey.template_question_select')
</script>
<script type="text/ng-template" id="checkboxs">
    @include('files.survey.template_question_checkboxs')
</script>
<script type="text/ng-template" id="scales">
    @include('files.survey.template_question_scales')
</script>
<script type="text/ng-template" id="texts">
    @include('files.survey.template_question_texts')
</script>
<script type="text/ng-template" id="list">
    @include('files.survey.template_question_list')
</script>

<script src="/js/ng/ngSurvey.js"></script>

<script>
app.requires.push('ngSurvey');
app.controller('quesController', function quesController($scope, $http, $filter, $window, $document, questionService, $rootScope){

    $scope.book = {};
    $scope.question;
    $scope.questions = [];
    
    var index = 0;

    $scope.getQuestions = function() {
        $scope.questions = [];
        $scope.$parent.main.loading = true;
        $http({method: 'POST', url: 'getQuestions', data:{record: $scope.record, book: $scope.book, baby: $scope.baby, page: $scope.currentPage}})
        .success(function(data, status, headers, config) {
            console.log(data);
            $scope.questions = data.page;
            $scope.question = $scope.questions[index];
            console.log($scope.question);
            $scope.$parent.main.loading = false;
        }).error(function(e) {
            console.log(e);
        });
    };

    $scope.getQuestions();

    $scope.next = function() {        
        $scope.question = $scope.questions[++index];
        console.log($scope.question);
    };

});
</script>
