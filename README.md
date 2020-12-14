# Intro

Evals are to be respected! **NEVER** run this on anything publically available! This is the keys to the castle for PHP. Anything PHP can do, eval will interperet it. Only warning...

## Commands

- Save `Ctrl`+`S`
- Last `Ctrl`+`R`
- Evaluate `Ctrl`+`Enter`
- Fresh Start `Ctrl`+`B`

## Setup on Windows

- Install [XAMPP](https://www.apachefriends.org/)
- Modify `httpd.conf`
  Default Location: `C:\xampp\apache\conf\httpd.conf`
  Search for `DocumentRoot` and replace it with the following

```
# Replace "LOCATION" with location of your project folder
DocumentRoot "LOCATION"
<Directory "LOCATION">
     Options Indexes FollowSymLinks Includes ExecCGI
     AllowOverride All
     Require all granted
</Directory>
```

- Restart Apache Service
- Download EvalPal to your project folder
- Go to location, example: `http://localhost/EvalPal/`

## Future Features/Ideas

- MySQL eval
- Javascript eval
- UI Rework to side by side panels

## Example Usage

#### Access files within project folder (PHP tag nolonger needed)

![](https://i.imgur.com/E90Mklm.png)

#### Test project for errors before production (PHP tag nolonger needed)

![](https://i.imgur.com/FufXyZd.png)
