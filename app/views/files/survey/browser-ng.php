<md-content ng-cloak layout="column" ng-controller="browser" layout-align="start center">
    <div node-browser>
    </div>
</md-content>
<script src="/js/ng/ngSurvey.js"></script>
<script>
    app.controller('browser', function ($scope, $http, $filter, $q){
    $scope.question_number=0;
        $scope.browserQuestion = function() {
            $http({method: 'POST', url: 'getQuestion', data:{}})
            .success(function(data, status, headers, config) {
                $scope.questions=$scope.nodeAnalysis(data.questions);
            })
            .error(function(e){
                console.log(e);
            });
        };
    $scope.checkType = function(question){
        switch(question.node.type){
            case "checkbox":
            return false;
            case "text":
            return false;
        }
        return true; 
    }

    $scope.checkRowspan = function(question){
        return (question.rowspan>=1)? true: false;
    }

    $scope.nodeAnalysis = function(node){
        var browser_node = node;
        var deal_node_id=[];
        for(var i=0;i<node.length;i++){
            node[i].rowspan = 0;
            //檢查node_id是否為重複
            if(deal_node_id.indexOf(node[i].node_id) == -1
                ||node[i].node.type == "scale" ){
                deal_node_id.push(node[i].node_id);
            }else{
                browser_node[i].rowspan = -1;
                continue;
            }
            //計算rowspan的長度
            for(var j=i;j<node.length;j++){
                if(browser_node[i].node_id==node[j].node_id){
                    if(browser_node[i].node.type == "scale"){
                        browser_node[i].rowspan = 1;
                        continue;
                    }else if(browser_node[i]){
                        browser_node[i].rowspan += 1;   
                    }
                }
            }
        }  
        console.log(browser_node);
        return browser_node;
    }
    $scope.browserQuestion();
    })
    .directive("nodeBrowser",function(){
        return {
            restrict : "A",
            templateUrl : "questionBrowser"
        };
    });
</script>