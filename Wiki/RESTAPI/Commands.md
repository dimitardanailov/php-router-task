```bash
# Add Article
curl --data "title=My First Post&__method=POST&date=2015-10-07&text=Lorem Ipsum" http://localhost/RouteTask/news
```

```bash
# Update Article

# Invalid Update
curl --data "title=My Second Post&__method=PUT&date=2015-10-07&text=Lorem Ipsum" http://localhost/RouteTask/news/1440

# Valid Update
curl --data "title=My Second Post&__method=PUT&date=2015-10-07&text=Lorem Ipsum" http://localhost/RouteTask/news/14
```

  