setwd('C:\\xampp\\htdocs\\recommendamazon')

options(repos=structure(c(CRAN="https://cran.cnr.berkeley.edu/")))


library(dplyr)
library(recommenderlab)
library(stringr)
library(tidyr)
library(optparse)


#install.packages("optparse")

args <- commandArgs(TRUE)   
N <- as.character(args[1])    


amazonproducts<-read.csv("amazonproducts.csv",stringsAsFactors = F)


recommendproduct<-as.data.frame(amazonproducts[,c(2,15,21)])


recommendproduct <- recommendproduct[!(is.na(recommendproduct$reviews.rating) | recommendproduct$reviews.rating==""), ]


recommendproduct <- recommendproduct[!(is.na(recommendproduct$name) | recommendproduct$name==""), ]

recommendproduct<-separate(recommendproduct, name, into="productname", sep = ",,,", remove = TRUE)

#Cleaning username- Removing rows with NA or empty usernames
recommendproduct <- recommendproduct[!(is.na(recommendproduct$reviews.username) | recommendproduct$reviews.username==""), ]



#Choose only those products that have at least N number of reviews
#Arranging products by descending order of reviews present for each user
by_username <- recommendproduct %>% group_by(reviews.username)
by_username <- by_username %>% summarise(n = n())
by_username <- by_username %>% arrange(desc(n))


#Choose only those products that have at least N number of reviews
#Arranging beers by descending order of reviews present for each product
by_product <- recommendproduct %>% group_by(productname)
by_product <- by_product %>% summarise(n = n())
by_product <- by_product %>% arrange(desc(n))


#Merge the original dataset where beer reviews >50 and user reviews greater than 30
product_final<-merge(recommendproduct,by_product,by.x="productname",by.y="productname")
product_final<-merge(product_final,by_username,by.x="reviews.username",by.y="reviews.username")

product_final$productname<-str_remove_all(product_final$productname, ",,")

rrm <- as(product_final[,c(1,2,3)], "realRatingMatrix")


# coerce the matrix to a dataframe
recommend_df <- as(rrm, "data.frame")


#Train and test
scheme <- evaluationScheme(rrm, method = "split", train = .9,
                           k = 1,given=1 ,goodRating = 4)


algorithms <- list(
  "user-based CF" = list(name="UBCF", param=list(normalize = "Z-score",
                                                 method="Cosine",
                                                 nn=30, minRating=3)),
  "item-based CF" = list(name="IBCF", param=list(normalize = "Z-score",
                                                 method="Cosine",
                                                 nn=30, minRating=3))
)

results <- evaluate(scheme, algorithms, n=c(1, 3, 5, 10, 15, 20))

#UBCF Model
UBCF_Predict <- Recommender(rrm, method = "UBCF")

IBCF_Predict <- Recommender(rrm, method = "IBCF")

username<-N


#Give top 3 products of given user using UBCF
UBCF <- predict(UBCF_Predict, rrm[username], n=3)
ubcf_top3<-as.data.frame(as(bestN(UBCF, n = 3),"list"))


#Give top 3 products of given user using IBCF
IBCF <- predict(IBCF_Predict, rrm[username], n=3)
ibcf_top3<-as.data.frame(as(bestN(IBCF, n = 3),"list"))


final<-rbind(ubcf_top3,ibcf_top3)

write.csv(final, 'C:\\xampp\\htdocs\\recommendamazon\\output.csv',row.names = FALSE)

tail(unique(product_final$reviews.username),100)
