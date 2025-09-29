server {
    listen 80;
    server_name _;

    root /var/www/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 👇 Esto es clave para que funcione /storage/*
    location /storage {
        alias /var/www/storage/app/public;
        access_log off;
        expires 30d;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
}
