/**
@Time    : 2021/9/15 19:15
@Author  : 郭建宇
@Email   : 276381225@qq.com
@File    : JWT.go
**/
package main

import (
    "flag"
    "github.com/dgrijalva/jwt-go"
    "fmt"
    "time"
)



//jwt token
var (
    TokenHeader   = "ldmwebtoken"
    TokenUser     = "ldmuser"
    TokenPassword = "ldmpassword"
    JwtSecret     = "http://ldm.lenovo.com"
    Iss =  "http://www.lenovo.com/"
    Aud = "http://www.lenovo.com/"
    tokenExpTime  = 60 //Minute
)

func CreateToken(user, secret,id string) (string, error) {
    // here, we have kept it as 15 minutes
    at := jwt.NewWithClaims(jwt.SigningMethodHS256, jwt.MapClaims{
        "Id": id,
        "iss": Iss,
        "aud": Aud,
        "exp":  time.Now().Add(time.Minute * time.Duration(tokenExpTime)).Unix(),
    })
    token, err := at.SignedString([]byte(secret))
    if err != nil {
        return "", err
    }
    return token, nil
}
func main(){
    var (
        user string
        secret string
        id string
    )
    flag.StringVar(&user, "user", "admin", "user")
    flag.StringVar(&secret, "secret", JwtSecret, "secret")
    flag.StringVar(&id,"id","1","id")
    flag.Parse()
    a,_:=CreateToken("admin",JwtSecret,id)
    fmt.Println(a)
}