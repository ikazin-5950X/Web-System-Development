screenを3枚以上出しておくと楽。  
このまま使うのであれば、  
```bash
cd Web-System-Development  
docker compose build
docker compose up  　　
```  
```bash
Error response from daemon: Conflict. The container name “/php” is already in use by container “**************244124ceaac17f4c115f2b6facd5e67d22763d80********“. You have to remove (or rename) that container to be able to reuse that name.  
```
↑なエラーが出たら、
```
bashdocker rm **************244124ceaac17f4c115f2b6facd5e67d22763d80********
```  
で既存コンテナ削除  
とりあえずエラーが出なくなるまで実行。  　　

出なくなったらDocker起動
```bash
docker compose up
```
2枚目のscreenでmysqlを起動
```bash
docker compose exec mysql mysql techc  　　
```
データベースを作る。
```mysql
CREATE TABLE `bbs_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `body` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
```mysql
ALTER TABLE `bbs_entries` ADD COLUMN image_filename TEXT DEFAULT NULL;
```
3枚目のscreenで内部のターミナルを起動
```bash
docker compose exec web bash  
```
ブラウザに権限付与させる
```bash
chmod 777 /var/www/upload/image/
```
