create table migrations
(
  id        int unsigned auto_increment
    primary key,
  migration varchar(191) not null,
  batch     int          not null
)
  collate = utf8mb4_unicode_ci;

create table password_resets
(
  email      varchar(191) not null,
  token      varchar(191) not null,
  created_at timestamp    null
)
  collate = utf8mb4_unicode_ci;

create index password_resets_email_index
  on password_resets (email);

create table subjects
(
  id                 bigint unsigned auto_increment
    primary key,
  code               varchar(10)          not null,
  name               varchar(100)         not null,
  uc                 int                  not null,
  is_final_subject   tinyint(1) default 0 not null,
  is_project_subject tinyint(1) default 0 not null,
  theoretical_hours  int        default 0 not null,
  practical_hours    int        default 0 not null,
  laboratory_hours   int        default 0 not null
)
  collate = utf8mb4_unicode_ci;

create table universities
(
  id      varchar(10)  not null
    primary key,
  name    varchar(100) not null,
  acronym varchar(10)  null,
  constraint universities_acronym_unique
    unique (acronym),
  constraint universities_name_unique
    unique (name)
)
  collate = utf8mb4_unicode_ci;

create table faculties
(
  id            varchar(10)  not null
    primary key,
  university_id varchar(10)  not null,
  name          varchar(100) not null,
  acronym       varchar(10)  null,
  constraint faculties_university_id_foreign
    foreign key (university_id) references universities (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table organizations
(
  id              varchar(10)  not null
    primary key,
  name            varchar(100) not null,
  faculty_id      varchar(10)  not null,
  organization_id varchar(10)  null,
  website         varchar(50)  not null,
  address         text         null,
  constraint organizations_faculty_id_foreign
    foreign key (faculty_id) references faculties (id)
      on delete cascade,
  constraint organizations_organization_id_foreign
    foreign key (organization_id) references organizations (id)
)
  collate = utf8mb4_unicode_ci;

create table school_periods
(
  id                     bigint unsigned auto_increment
    primary key,
  organization_id        varchar(10)          not null,
  cod_school_period      varchar(10)          not null,
  start_date             date                 not null,
  end_date               date                 not null,
  withdrawal_deadline    date                 not null,
  load_notes             tinyint(1) default 0 not null,
  inscription_start_date date                 not null,
  inscription_visible    tinyint(1) default 0 not null,
  project_duty           double(8, 2)         not null,
  final_work_duty        double(8, 2)         not null,
  constraint school_periods_organization_id_foreign
    foreign key (organization_id) references organizations (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table school_programs
(
  id                      bigint unsigned auto_increment
    primary key,
  organization_id         varchar(10)          not null,
  school_program_name     varchar(100)         not null,
  num_cu                  int                  null,
  min_num_cu_final_work   int                  null,
  duration                int                  null,
  min_duration            int                  null,
  grant_certificate       tinyint(1) default 0 not null,
  conducive_to_degree     tinyint(1) default 1 not null,
  doctoral_exam           tinyint(1) default 0 not null,
  min_cu_to_doctoral_exam int                  null,
  constraint school_programs_organization_id_foreign
    foreign key (organization_id) references organizations (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table school_program_subject
(
  id                bigint unsigned auto_increment
    primary key,
  school_program_id bigint unsigned not null,
  subject_id        bigint unsigned not null,
  type              varchar(2)      null,
  subject_group     int             null,
  constraint school_program_subject_school_program_id_foreign
    foreign key (school_program_id) references school_programs (id)
      on delete cascade,
  constraint school_program_subject_subject_id_foreign
    foreign key (subject_id) references subjects (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table users
(
  id                bigint unsigned auto_increment
    primary key,
  identification    varchar(20)          not null,
  first_name        varchar(20)          not null,
  second_name       varchar(20)          null,
  first_surname     varchar(20)          not null,
  second_surname    varchar(20)          null,
  telephone         varchar(15)          null,
  mobile            varchar(15)          not null,
  work_phone        varchar(15)          null,
  email             varchar(30)          not null,
  email_verified_at timestamp            null,
  password          varchar(250)         not null,
  level_instruction varchar(3)           not null,
  active            tinyint(1) default 1 not null,
  with_disabilities tinyint(1) default 0 not null,
  sex               varchar(1)           not null,
  nationality       varchar(1)           not null,
  organization_id   varchar(10)          not null,
  remember_token    varchar(100)         null,
  constraint users_organization_id_foreign
    foreign key (organization_id) references organizations (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table administrators
(
  id        bigint unsigned      not null
    primary key,
  rol       varchar(11)          not null,
  principal tinyint(1) default 0 not null,
  constraint administrators_id_foreign
    foreign key (id) references users (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table logs
(
  id              bigint unsigned auto_increment
    primary key,
  user_id         bigint unsigned not null,
  log_description varchar(200)    not null,
  created_at      timestamp       null,
  updated_at      timestamp       null,
  constraint logs_user_id_foreign
    foreign key (user_id) references users (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table roles
(
  id        bigint unsigned auto_increment
    primary key,
  user_id   bigint unsigned not null,
  user_type varchar(1)      not null,
  constraint roles_user_id_foreign
    foreign key (user_id) references users (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table teachers
(
  id             bigint unsigned not null
    primary key,
  teacher_type   varchar(3)      not null,
  dedication     varchar(3)      not null,
  category       varchar(3)      not null,
  home_institute varchar(100)    null,
  country        varchar(20)     null,
  constraint teachers_id_foreign
    foreign key (id) references users (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table school_period_subject_teacher
(
  id                bigint unsigned auto_increment
    primary key,
  teacher_id        bigint unsigned not null,
  subject_id        bigint unsigned not null,
  school_period_id  bigint unsigned not null,
  `limit`           int             not null,
  enrolled_students int             not null,
  duty              double(8, 2)    not null,
  modality          varchar(3)      not null,
  start_date        date            null,
  end_date          date            null,
  constraint school_period_subject_teacher_school_period_id_foreign
    foreign key (school_period_id) references school_periods (id)
      on delete cascade,
  constraint school_period_subject_teacher_subject_id_foreign
    foreign key (subject_id) references subjects (id)
      on delete cascade,
  constraint school_period_subject_teacher_teacher_id_foreign
    foreign key (teacher_id) references teachers (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table schedules
(
  school_period_subject_teacher_id bigint unsigned not null,
  day                              varchar(1)      not null,
  classroom                        varchar(40)     not null,
  start_hour                       time            not null,
  end_hour                         time            not null,
  constraint schedules_school_period_subject_teacher_id_foreign
    foreign key (school_period_subject_teacher_id) references school_period_subject_teacher (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table students
(
  id                      bigint unsigned auto_increment
    primary key,
  school_program_id       bigint unsigned          not null,
  user_id                 bigint unsigned          not null,
  guide_teacher_id        bigint unsigned          null,
  student_type            varchar(3)               not null,
  home_university         varchar(100)             not null,
  current_postgraduate    varchar(100)             null,
  type_income             varchar(30)              null,
  is_ucv_teacher          tinyint(1) default 0     not null,
  is_available_final_work tinyint(1) default 0     not null,
  credits_granted         int        default 0     null,
  with_work               tinyint(1) default 0     null,
  end_program             tinyint(1) default 0     not null,
  test_period             tinyint(1) default 0     not null,
  current_status          varchar(5) default 'REG' not null,
  allow_post_inscription  tinyint(1) default 0     not null,
  constraint students_guide_teacher_id_foreign
    foreign key (guide_teacher_id) references teachers (id)
      on delete cascade,
  constraint students_school_program_id_foreign
    foreign key (school_program_id) references school_programs (id)
      on delete cascade,
  constraint students_user_id_foreign
    foreign key (user_id) references users (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table degrees
(
  student_id         bigint unsigned not null,
  degree_obtained    varchar(3)      not null,
  degree_name        varchar(50)     not null,
  degree_description varchar(200)    null,
  university         varchar(100)    not null,
  constraint degrees_student_id_foreign
    foreign key (student_id) references students (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table equivalences
(
  id            bigint unsigned auto_increment
    primary key,
  student_id    bigint unsigned not null,
  subject_id    bigint unsigned not null,
  qualification int             not null,
  constraint equivalences_student_id_foreign
    foreign key (student_id) references students (id)
      on delete cascade,
  constraint equivalences_subject_id_foreign
    foreign key (subject_id) references subjects (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table final_works
(
  id            bigint unsigned auto_increment
    primary key,
  title         varchar(100)         not null,
  student_id    bigint unsigned      not null,
  subject_id    bigint unsigned      not null,
  project_id    bigint unsigned      null,
  is_project    tinyint(1) default 0 not null,
  approval_date date                 null,
  constraint final_works_project_id_foreign
    foreign key (project_id) references final_works (id)
      on delete cascade,
  constraint final_works_student_id_foreign
    foreign key (student_id) references students (id)
      on delete cascade,
  constraint final_works_subject_id_foreign
    foreign key (subject_id) references subjects (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table advisors
(
  id            bigint unsigned auto_increment
    primary key,
  final_work_id bigint unsigned not null,
  teacher_id    bigint unsigned not null,
  constraint advisors_final_work_id_foreign
    foreign key (final_work_id) references final_works (id)
      on delete cascade,
  constraint advisors_teacher_id_foreign
    foreign key (teacher_id) references teachers (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table school_period_student
(
  id                    bigint unsigned auto_increment
    primary key,
  student_id            bigint unsigned                   not null,
  school_period_id      bigint unsigned                   not null,
  status                varchar(5)                        not null,
  financing             varchar(3)                        null,
  financing_description text                              null,
  pay_ref               varchar(50)                       null,
  amount_paid           double(8, 2) default 0.00         null,
  inscription_date      date         default '2021-04-27' not null,
  test_period           tinyint(1)   default 0            null,
  constraint school_period_student_school_period_id_foreign
    foreign key (school_period_id) references school_periods (id)
      on delete cascade,
  constraint school_period_student_student_id_foreign
    foreign key (student_id) references students (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table doctoral_exams
(
  school_period_student_id bigint unsigned not null,
  status                   varchar(10)     not null,
  created_at               timestamp       null,
  updated_at               timestamp       null,
  constraint doctoral_exams_school_period_student_id_foreign
    foreign key (school_period_student_id) references school_period_student (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table final_work_school_period
(
  id                       bigint unsigned auto_increment
    primary key,
  status                   varchar(10) default 'progress' not null,
  description_status       text                           null,
  final_work_id            bigint unsigned                not null,
  school_period_student_id bigint unsigned                not null,
  constraint final_work_school_period_final_work_id_foreign
    foreign key (final_work_id) references final_works (id)
      on delete cascade,
  constraint final_work_school_period_school_period_student_id_foreign
    foreign key (school_period_student_id) references school_period_student (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;

create table student_subject
(
  id                               bigint unsigned auto_increment
    primary key,
  school_period_student_id         bigint unsigned not null,
  school_period_subject_teacher_id bigint unsigned not null,
  qualification                    int             null,
  status                           varchar(3)      not null,
  constraint student_subject_school_period_student_id_foreign
    foreign key (school_period_student_id) references school_period_student (id)
      on delete cascade,
  constraint student_subject_school_period_subject_teacher_id_foreign
    foreign key (school_period_subject_teacher_id) references school_period_subject_teacher (id)
      on delete cascade
)
  collate = utf8mb4_unicode_ci;


