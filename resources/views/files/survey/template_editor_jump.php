<div layout="column" layout-padding>
    <div layout="row">
        <md-input-container flex>
            <label>選擇資料檔</label>
            <md-select flex ng-model="question.file" placeholder="選擇資料檔" class="md-no-underline"  md-on-open="getRowsFiles()" ng-change="getColumns()">
                <md-option ng-repeat="rowsFile in rowsFiles" ng-value="rowsFile.id">{{rowsFile.title}}</md-option>
            </md-select>
        </md-input-container>
        <md-input-container flex>
            <label>選擇欄位</label>
            <md-select ng-model="answer.jump" placeholder="選擇欄位" class="md-no-underline">
                <md-option ng-repeat="column in columns" ng-value="column.id">{{column.title}}</md-option>
            </md-select>
        </md-input-container>
    </div>
    <md-divider></md-divider>
    <div ng-repeat="answer in question.answers" layout="row">
        <md-input-container flex>
            <label>當這個欄位值等於...時，開啟</label>
            <input required type="text" ng-model="project.rate" md-no-asterisk />
        </md-input-container>
        <md-input-container flex>
            <label>選擇題本</label>
            <md-select ng-model="answer.jump" placeholder="選擇題本"  md-on-open="getBooks()">
                <md-option ng-repeat="book in books" ng-value="book.id">{{book.title}}</md-option>
            </md-select>
        </md-input-container>
        <md-input-container>
        <md-button class="md-icon-button" style="padding-top:0" ng-click="removeAnswer(answer)">
            <md-icon aria-label="刪除選項" md-svg-icon="delete"></md-icon>
        </md-button>
        </md-input-container>
    </div>
    <md-list-item ng-click="createAnswer(question)">
        <md-icon md-svg-icon="mode-edit"></md-icon>
        <p>新增開啟題本的條件</p>
    </md-list-item>
</div>