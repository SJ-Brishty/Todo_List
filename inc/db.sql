# Errstellen user_table
Use mariadb;
create or replace table user_table(
    id int auto_increment,
    name varchar(255) not null,
    password varchar(255) not null,
    primary key(id)
);

# Errstellen todo_table
Use mariadb;
create or replace table todo_table(
    id int auto_increment,
    UserId int not null,
    Datum Date not null,
    todo varchar(255) not Null,
    FOREIGN KEY (UserId) REFERENCES user_table (id),
    primary key(id)
);

# Daten eintragen
Use mariadb;
INSERT INTO user_table (name, password)
VALUES ('Thea', '0000'),
('Lara', '1111'),
('Luisa', '2222');
