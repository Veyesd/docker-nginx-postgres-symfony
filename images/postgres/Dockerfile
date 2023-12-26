FROM postgres
RUN --name some-postgres -e POSTGRES_USER=postgres POSTGRES_PASSWORD=mysecretpassword POSTGRES_DB=postgres -d postgres
# RUN --name some-postgres -e POSTGRES_PASSWORD_FILE=/run/secrets/postgres-passwd -d postgres
RUN -it --rm --network some-network postgres psql -h some-postgres -U postgres
