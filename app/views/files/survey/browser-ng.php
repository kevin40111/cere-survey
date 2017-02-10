<md-content ng-cloak layout="column" ng-controller="browser" layout-align="start center">
    <div node-browser ></div>
</md-content>
<script>
    app.controller('browser', function (){})
    .directive("nodeBrowser",function($http,$mdDialog){
        return {
            restrict : "A",
            templateUrl : "questionBrowser",
            scope:{},
            link: function(scope, element, attrs) {

            scope.showPassQuestion = function(node) {
                if(node.rules.length<=0)return;
                $mdDialog.show(
                $mdDialog.alert()
                .parent(angular.element(document.querySelector('#popupContainer')))
                .clickOutsideToClose(true)
                .textContent('跳題條件')
                .ok('確認')
                );
            };

            scope.browserQuestion = function() {
                $http({method: 'POST', url: 'getQuestion', data:{}})
                .success(function(data, status, headers, config) {
                scope.questions=scope.questionAnalysis(data.questions);
            }).error(function(e){
                console.log(e);
            });
            };
            scope.browserQuestion();

            scope.checkQuestionType = function(question){
            switch(question.node.type){
                case "checkbox":
                return false;
                case "text":
                return false;
            }
            return true; 
            }

            scope.translateQustionType = function(question_type){
                switch(question_type){
                    case "radio":
                    return "單選題";
                    break;

                    case "text":
                    return "文字填答";
                    break;

                    case "scale":
                    return "量表題";
                    break;

                    case "checkbox":
                    return "複選題";
                    break;

                    case "select":
                    return "下拉式選單";
                    break;

                    case "explain":
                    return "說明文字";
                    break;

                }
            }

            scope.checkParentNodeType = function(question){
                 var question_split = question.node.parent_type.split("\\");
                 return  question_split[question_split.length-1];  
            }

            // 子題用
             scope.getParentNode = function(question,key){
                var node={};
                node.parent_id = question.node.parent_id;
                node.parent_question_type = scope.checkParentNodeType(question);
                var txt = "(第";
                 switch(node.parent_question_type){
                    case "Answer":
                        for(var i=key;i>=0;--i){
                           for(var j=0;j<this.questions[i].node.answers.length;j++){
                                if(this.questions[i].node.answers[j].id == node.parent_id){
                                    txt += this.questions[i].question_number+"題 選項-"
                                    txt += this.questions[i].node.answers[j].title;
                                    txt += " 子題";
                                }
                           }
                        }
                    break;
                     case "Question":
                        for(var i=key;i>=0;--i){
                           if(this.questions[i].id == node.parent_id){
                                txt += this.questions[i].question_number+"題 選項-"
                                txt += this.questions[i].title;
                                txt += " 子題";
                            }
                        }
                    break;
                }
                txt += ")";

                if(node.parent_question_type != "Node") return txt;
            }

            scope.getQuestionTitle = function(question){
            this.question = question;
            question.question_title = [];
            switch(question.node.type)
            {   
                case "radio":
                   question.question_title["title"] = question.title; 
                break;
                case "checkbox":
                   question.question_title["title"] = question.node.title; 
                break;
                case "select":
                   question.question_title["title"] = question.title; 
                break;
                case "scale":
                   question.question_title["title"] = question.node.title+"："+question.title; 
                break;
                case "text":
                   question.question_title["title"] = question.node.title; 
                break;
                case "explain":
                   question.question_title["title"] = question.node.title; 
                break;

            }
                return this.question;
            }

            scope.questionAnalysis = function(node){
            var browser_node = node;
            var page = 1;
            var page_record = [];// 檢查parent_node_id 重複
            var answer_number;
            var question_number=0;
            var deal_node_id=[];// 檢查重複出現的node_id
           
            for(var i=0;i<browser_node.length;i++){
                browser_node[i] = scope.getQuestionTitle(browser_node[i]);
                if(page_record.indexOf(browser_node[i].node.parent_id) == -1 && scope.checkParentNodeType(browser_node[i]) == "Node"){
                    page_record.push(browser_node[i].node.parent_id);
                    browser_node[i].page = page;
                    page++;
                }
                //檢查node_id是否為重複 
                if(deal_node_id.indexOf(node[i].node_id) == -1){
                    answer_number = 1;
                    deal_node_id.push(node[i].node_id);
                    browser_node[i].question_number = ++question_number;
                    browser_node[i].answer_number = answer_number;

                }else if(node[i].node.type == "scale" ){
                    deal_node_id.push(node[i].node_id);
                    browser_node[i].question_number = question_number;

                }else{
                    browser_node[i].answer_number = ++answer_number;
                    browser_node[i].rowspan = 0;
                    continue;
                }
                //計算rowspan的長度
                browser_node[i].rowspan = 0;
                browser_node[i].question_title["rowspan"] = 1;
                for(var j=i;j<node.length;j++){
                    if(browser_node[i].node_id==node[j].node_id){
                        if(browser_node[i].node.type == "scale"){
                            browser_node[i].question_title["rowspan"]++;
                            browser_node[i].rowspan = 1;
                            continue;
                        }else{
                            browser_node[i].rowspan++;   
                        }
                    }
                }
            }  
            console.log(browser_node);
            this.questions=browser_node;
            return this.questions;
            }

            }
        };
    });
</script>

