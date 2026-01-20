CREATE DATABASE th_v2;
USE th_v2;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

CREATE TABLE candidates (
    user_id INT PRIMARY KEY,
    cv_path VARCHAR(255) NULL,
    expected_salary DECIMAL(10, 2) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE recruiters (
    user_id INT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    company_description TEXT NULL,
    website_url VARCHAR(255) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(100),
    salary DECIMAL(10, 2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    FOREIGN KEY (recruiter_id) REFERENCES recruiters(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

CREATE TABLE offer_tags (
    offer_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (offer_id, tag_id),
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

CREATE TABLE candidate_tags (
    candidate_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (candidate_id, tag_id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(user_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    offer_id INT NOT NULL,
    candidate_id INT NOT NULL,
    cv_attachment VARCHAR(255) NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(user_id) ON DELETE CASCADE
);

INSERT INTO roles (name) VALUES ('admin'), ('recruiter'), ('candidate');

INSERT INTO users (role_id, first_name, last_name, username, email, password) VALUES 
(1, 'Admin', 'User', 'admin_account', 'admin@talenthub.com', '$2y$12$4bk/nDrWnvKFHOoJgeVtdu6ztbPMEuOU46JtngIkBfIeWAmhil3Gm'),
(2, 'Michael', 'Scott', 'mscott_hr', 'michael@dundermifflin.com', '$2y$12$4bk/nDrWnvKFHOoJgeVtdu6ztbPMEuOU46JtngIkBfIeWAmhil3Gm'),
(2, 'Thomas', 'Wayne', 'twayne_ceo', 'thomas@waynecorp.com', '$2y$12$4bk/nDrWnvKFHOoJgeVtdu6ztbPMEuOU46JtngIkBfIeWAmhil3Gm'),
(3, 'Peter', 'Parker', 'spiderman_dev', 'peter.parker@outlook.com', '$2y$12$4bk/nDrWnvKFHOoJgeVtdu6ztbPMEuOU46JtngIkBfIeWAmhil3Gm'),
(3, 'Bruce', 'Banner', 'hulk_data', 'b.banner@gmail.com', '$2y$12$4bk/nDrWnvKFHOoJgeVtdu6ztbPMEuOU46JtngIkBfIeWAmhil3Gm');

INSERT INTO recruiters (user_id, company_name, company_description, website_url) VALUES 
(2, 'Dunder Mifflin', 'Paper distribution company.', 'https://dundermifflin.com'),
(3, 'Wayne Enterprises', 'Global conglomerate and tech leader.', 'https://waynecorp.com');

INSERT INTO candidates (user_id, cv_path, expected_salary) VALUES 
(4, 'uploads/cv/peter_parker_cv.pdf', 55000.00),
(5, 'uploads/cv/bruce_banner_cv.pdf', 150000.00);

INSERT INTO categories (name) VALUES ('Web Development'), ('Data Science'), ('Management'), ('DevOps');

INSERT INTO tags (name) VALUES ('PHP'), ('JavaScript'), ('Python'), ('AWS'), ('React'), ('MySQL');

INSERT INTO offers (recruiter_id, category_id, title, description, location, salary) VALUES 
(2, 3, 'Regional Manager', 'Lead the Scranton branch to success.', 'Scranton, PA', 60000.00),
(3, 1, 'Fullstack Developer', 'Building secure web portals using PHP and React.', 'Gotham City', 95000.00),
(3, 2, 'Senior Data Scientist', 'Deep learning and Gamma radiation analysis.', 'Remote', 140000.00);

INSERT INTO offer_tags (offer_id, tag_id) VALUES 
(2, 1), (2, 5), (2, 6),
(3, 3), (3, 6);

INSERT INTO candidate_tags (candidate_id, tag_id) VALUES 
(4, 1), (4, 2), (4, 5),
(5, 3), (5, 6);

INSERT INTO applications (offer_id, candidate_id, cv_attachment, status) VALUES 
(2, 4, 'uploads/cv/peter_application_final.pdf', 'pending');