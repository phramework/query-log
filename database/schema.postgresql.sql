--
-- PostgreSQL database dump
--

-- Dumped from database version 9.4.5
-- Dumped by pg_dump version 9.4.5
-- Started on 2015-12-14 16:18:49 EET

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = log_store, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 215 (class 1259 OID 32903)
-- Name: query_log; Type: TABLE; Schema: log_store; Owner: logger; Tablespace:
--

CREATE TABLE query_log (
  id bigint DEFAULT nextval('log_seq'::regclass) NOT NULL,
  request_id text NOT NULL,
  query text NOT NULL,
  parameters json,
  start_timestamp bigint NOT NULL,
  duration integer NOT NULL,
  function text NOT NULL,
  "URI" text NOT NULL,
  "method" text,
  additional_parameters json,
  call_trace json,
  user_id bigint,
  user_uuid text,
  exception text
);

--
-- TOC entry 1990 (class 2606 OID 32911)
-- Name: query_log_pk; Type: CONSTRAINT; Schema: log_store; Owner: logger; Tablespace:
--

ALTER TABLE ONLY query_log
  ADD CONSTRAINT query_log_pk PRIMARY KEY (id);


-- Completed on 2015-12-14 16:18:55 EET

--
-- PostgreSQL database dump complete
--
