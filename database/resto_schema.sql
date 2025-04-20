create table resto_table
(
    table_number  integer not null
        constraint resto_table_pk
            primary key autoincrement,
    table_name    text    not null,
    seat_capacity integer not null
);

-- Insert Left side tables (L1 to L5)
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('L1', 4);
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('L2', 6);
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('L3', 8);
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('L4', 10);
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('L5', 12);

-- Insert Right side tables (R1 to R5)
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('R1', 6);
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('R2', 8);
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('R3', 4);
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('R4', 10);
INSERT INTO resto_table (table_name, seat_capacity) VALUES ('R5', 8);


create table table_states
(
    fk_table_name text
        constraint table_states_resto_table_table_name_fk
            references resto_table (table_name),
    guest_count   integer not null,
    timestamp     text    not null,
    table_status  text    not null
);

-- Empty tables
INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('L1', 0, datetime('now'), 'empty');

INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('L5', 0, datetime('now'), 'empty');

INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('R2', 0, datetime('now'), 'empty');

INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('R5', 0, datetime('now'), 'empty');

-- Busy tables (with appropriate guest counts)
INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('L2', 5, datetime('now', '-1 hour'), 'busy');

INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('L3', 7, datetime('now', '-2 hours'), 'busy');

INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('R1', 6, datetime('now', '-30 minutes'), 'busy');

-- Reserved tables (with planned guest counts)
INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('L4', 8, datetime('now', '+2 hours'), 'reserved');

INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('R3', 4, datetime('now', '+1 hour'), 'reserved');

INSERT INTO table_states (fk_table_name, guest_count, timestamp, table_status)
VALUES ('R4', 9, datetime('now', '+3 hours'), 'reserved');
