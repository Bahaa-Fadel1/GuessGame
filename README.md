# 1) Open terminal
# Ctrl + Alt + T

# 2) Install and start Docker (one time only)
sudo apt update
sudo apt install docker.io -y
sudo systemctl start docker

# 3) Download the project
git clone https://github.com/USERNAME/GuessGame.git
cd GuessGame

# 4) Build the project (first time only)
sudo docker build -t guessgame .

# 5) Run the project (first time only)
sudo docker run -d --name guessgame_app -p 8080:80 guessgame

# 6) If you restart Linux or Docker later, run this
sudo docker start guessgame_app

# 7) Open the game in the browser
xdg-open http://127.0.0.1:8080

#The application is a web-based game and can be accessed through any browser at:

http://127.0.0.1:8080
