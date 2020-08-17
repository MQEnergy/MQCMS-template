本项目基于hyperf2.0开发的快速应用开发框架，感谢hyperf的作者提供了这么优秀的框架

### 项目结构
```
Laidanme                           
├─ service                       // 服务层 提供接口等服务
└─ backend                       // 后台前端
```

## 一、运行后台前端
### 1、安装依赖
```
cd backend
yarn install / npm install #安装依赖包
```
### 2、运行项目
```
yarn run serve / npm run serve  #运行项目
```

## 二、运行服务端
### 1、docke环境开发（非必须）
window10以下环境安装`docker toolbox`。

##### 1）下载hyperf框架docker镜像
```
docker pull hyperf/hyperf
```
##### 2）下载laidanme系统到本地
```
# 例如：将项目放在本地e:/web/MQCMS-template
git clone https://github.com/MQEnergy/MQCMS-template
```
##### 2）进入docker运行命令
```
docker run -it -v /e/web/MQCMS-template/service:/mqcms -p 9507:9507 --name ldserver --entrypoint /bin/sh hyperf/hyperf
```
##### 3）将Composer镜像设置为阿里云镜像
```
cd laidanme
php bin/composer.phar config -g repo.packagist composer https://mirrors.aliyun.com/composer
```
##### 4）docker安装redis
```
docker pull redis
# 进入redis 配置redis可外部访问

docker run -d --privileged=true -p 6379:6379 -v /e/web/MQCMS-template/service/docker/conf/redis/redis.conf:/etc/redis/redis.conf --name mqredis redis redis-server /etc/redis/redis.conf --appendonly yes
docker exec -it mqredis /bin/sh

# 修改映射在本地的redis.conf
# 修改bind如下（根据自己熟悉程度配置）
# bind 0.0.0.0

# 可开启password（自行按需修改）
# requirepass foobared

# 重启redis
docker restart mqredis
```
##### 5）进入项目安装依赖启动项目
```
docker exec -it ldserver /bin/sh
cd mqcms
php bin/composer.phar install
cp .env.example .env  # 修改.env的配置
```
### 2、项目初始化
```
php bin/hyperf.php mq:init

 是否执行migrate?:
  [0] No
  [1] Yes
 > 1
# migrate是数据库迁移

 是否初始化所有Model类？:
  [0] No
  [1] Yes
 > 1

 是否基于Model初始化所有Service类？:
  [0] No
  [1] Yes
 > 1

 是否初始化所有Controller类？:
  [0] No
  [1] Yes
 > 1

 是否初始化一个后台账号密码？:
  [0] No
  [1] Yes
 > 1
# 选择1 出现如下输入框
 账号:
 > 
 密码:
 > 

Initialization successfully.
```
### 3、访问项目
```
php bin/hyperf.php start
访问：
http://127.0.0.1:9507

访问以下地址可生产测试token：
http://127.0.0.1:9507/backend/token/create 

访问以下地址可解析token
http://127.0.0.1:9507/backend/token/index 

```
## 三、扩展功能
### 1、生成model
```
php bin/hyperf.php gen:model --path=app/Model/Common --with-comments category
```
### 2、command命令扩展
#### 1）创建service
1、查看mq:service命令帮助
```
php bin/hyperf.php mq:service --help
```
2、创建默认命名空间的service（App\Service\Common）
```
# FooAdminService：service名称 FooAdmin：model名称 backend: model对应模块名称
php bin/hyperf.php mq:service FooService Foo backend
```
3、创建其他命名空间的service
```
# FooAdminService：service名称 FooAdmin：model名称 backend: model对应模块名称
php bin/hyperf.php mq:service -N App\\Service\\Admin FooAdminService FooAdmin backend
```
#### 2）创建controller
1、查看mq:controller命令帮助
```
php bin/hyperf.php mq:controller --help
```
2、创建默认命名空间的controller（App\Controller\Backend）
```
# FooController：controller名称 FooService：service名称 backend：service对应模块名称（后台，接口 可扩展，eg.可写成：Admin ADMIN backend ...）
php bin/hyperf.php mq:controller FooController FooService backend
```
3、创建其他命名空间的controller
```
# FooController：controller名称 FooService：service名称 backend：service对应模块名称（后台，接口 可扩展，eg.可写成：Api API api ...）
php bin/hyperf.php mq:controller -N App\\Controller\\Api\\V1 FooController FooService backend
```
## 四、常见问题
### 1、自动初始化命令后访问接口出现 Signature verification failed
答：由于初始化命令生成的是后端接口代码，请使用/backend/token/index 生成一个token 然后请求接口在header中加入Authorization参数 值为 Bearer token值
### 2、访问接口出现 Key may not be empty 
答：.env配置增加 JWT_FRONTEND_KEY，JWT_BACKEND_KEY 参数值，注意：参数名称FRONTEND, BACKEND对应app\Controller里面的模块名称，如添加其他模块，请增加JWT的其他模块参数
### 3、start项目出现错误  Uncaught RuntimeException: The class reflector object does not init yet
答：执行以下命令
```
composer dumpautoload -o
```
然后在执行
```
php bin/hyperf.php start
```
### 4、请求接口出现 Connection refused
答：请在.env中配置redis参数
### 5、.env配置中的REDIS_ON, APP_BACKEND_MUTEX，APP_FRONTEND_MUTEX有啥作用？
答：REDIS_ON是是否开启redis参数，设置为true是配合APP_BACKEND_MUTEX或者APP_FRONTEND_MUTEX使用的，具有相同账号在不同设备登录互斥的作用。注意：其中参数名的BACKEND和FRONTEND对应app\Controller里面的模块名称。
