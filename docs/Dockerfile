FROM ruby:2.6.3 as builder

WORKDIR /usr/share/build
COPY . .

RUN gem install bundler -v '2.0.2'
RUN apt update && apt install -y --no-install-recommends git;
RUN bundle install
RUN JEKYLL_ENV=production bundle exec jekyll build

FROM nginx:alpine

COPY ./default.conf /etc/nginx/conf.d/default.conf
COPY --from=builder /usr/share/build/_site /usr/share/nginx/html
