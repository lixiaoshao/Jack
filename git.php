------慕课网
git下载安装   yum install git


linux检项目到本地  git clone 地址

linux提交文件
git status  查看工作区的状态
git add 建立跟踪
git commit 提交

git push  提交到工作区



代码冲突：
git pull   拉下冲突的文件
git diff   比较文件存在的差异


------廖雪峰

创建并切换dev分支： git checkout -b dev
查看当前分支：git branch

修改readme.txt文件提交到当前分支：
git add readme.txt
git commit -m '提交说明'


切换master分支：git checkout master

dev分支的工作结果合并到master(合并模式：快进模式):   git merge dev

删除dev分支：git branch -d dev

---------------------------------------------------
在www.jack66li.top主机上测试整个流程   https://github.com/lixiaoshao/Jack.git
---------------------------------------------------

1.创建版本库： mkdir git_test
2.初始化git仓库： git init
3.新建文件并添加数据： vi readme.txt
4.把文件添加到仓库： git add  readme.txt
5.提交文件：git commit -m 'this is a file!'

6.再修改readme.txt  查看仓库当前状态 git status 
7.比较修改前后文件的区别：git diff
8.再次提交：git add readme.txt   git commit -m 'this is a modify!'

9.git log 查看文件修改日志

-------Git本地仓库与Github远程仓库关联
10.创建github账户：lixiaoshao  创建版本库   Jack
11.关联远程库：git remote add originJack git@github.com:lixiaoshao/Jack.git
git remote add originEdu git@github.com:lixiaoshao/edu.git
12. git push -u origin master  推送master分支的所有内容到远程库
13. 第一次出现错误(使用命令git push -f 强制覆盖远程,成功了)
14. 克隆远程库：git clone git@github.com:lixiaoshao/Jack.git
15.创建合并分支：
创建并切换分支：git checkout -b dev
提交文件：git add readme.txt
git commit -m 'create a dev!'
切换分支：git checkout master
合并分支：git merge dev
删除分支：git branch -d dev
git log --graph 查看分支合并图

bug分支：
有一种情况：就是在当前分支上修改文件，但是需要其它分支修改其他文件，而当前分支的文件又不能提交，所以使用 git stash  临时储存工作区
查看临时工作区：git stash list
恢复工作区：两种方法：
一、git stash apply  恢复后stash内容并不删除,需要使用git stash drop来删除
二、git stash pop  恢复及删除

Feature分支：
有一种情况，就是我们在一个分支上创建的文件提交后，但是不需要了
使用git branch -d dev  删除该分支会报错，使用git branch -D dev删除就可以了

16.多人协作：
查看远程库信息： git remote -v
从本地推送分支：git push originJack dev,推送失败,用git pull 抓取
在本地创建和远程对应的分支：
git checkout -b dev originJack/dev
建立本地分支和远程分支的关联：
git branch --set-upstream dev originJack/dev




