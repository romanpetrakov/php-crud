array (
  1 => 'create table node (id int not null auto_increment primary key) engine InnoDB default charset utf8;',
  2 => 'alter table node add column title varchar(250) not null default ""',
  3 => 'create table term (id int not null auto_increment primary key) engine InnoDB default charset utf8;',
  4 => 'alter table term add column title varchar(250) not null default "";',
  5 => 'alter table term add column parent_id int null;',
  6 => 'alter table term add foreign key (parent_id) references term(id);',
  7 => 'create table termtonode (id int not null auto_increment primary key) engine InnoDB default charset utf8;',
  8 => 'alter table termtonode add column node_id int not null;',
  9 => 'alter table termtonode add column term_id int not null;',
  10 => 'alter table termtonode add foreign key(node_id) references node(id);',
  11 => 'alter table termtonode add foreign key(term_id) references term(id);',
  12 => 'alter table termtonode add unique key (term_id, node_id);',
)