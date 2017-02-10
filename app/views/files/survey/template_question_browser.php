
<table class="ui celled structured table browserTable" style="font-family: Microsoft JhengHei">
        <tr style="font-size: 18px;">
            <td><b>項目</b></td>
            <td><b>題目</b></td>
            <td><b>題目代號</b></td>
            <td><b>題型</b></td>
            <td><b>選項</b></td>
            <td><b>頁數</b></td>
        </tr>
    
        <tr ng-repeat="(key,question) in questions">
            <!-- 題號 -->
            <td  ng-if="question.rowspan>=1"  rowspan="{{question.rowspan}}" style="text-align: center">{{question.question_number}}</td>
            <!-- 題目 -->
            <td  ng-click="showPassQuestion(question)" ng-if="question.rowspan>=1" rowspan="{{question.rowspan}}">
                {{getParentNode(question,key)}}
                {{question.question_title["title"]}}
                <span ng-if="question.rules.length > 0" class="ui left pointing red basic label">{{question.rules.length}}個跳題條件</span>
            </td>
            
             <!-- 題目代號 -->
             <td  ng-click="showPassQuestion(question)" ng-if="question.rowspan>=1" rowspan="{{question.rowspan}}">
                {{question.id}}
            </td>

            <!-- 題型 -->
            <td  ng-if="(question.rowspan>=1)"  rowspan="{{question.rowspan}}" >
                {{translateQustionType(question.node.type)}}
            </td>
            
            <!-- 選項 -->
            <td>
                <span ng-repeat="(key,answer) in question.node.answers">
                    {{key+1}}.{{answer.title}}
                    
                </span>
                <span ng-if="!checkQuestionType(question)">
                    {{question.answer_number}}.{{question.title}}
                     
                </span> <!-- 只印出複選題選項 -->
            </td>
             <td ng-if="question.page > 0"><b>第{{question.page}}頁</b></td><td rowspan="" ng-if="!(question.page > 0)"></td>
             
        </tr>
</table>