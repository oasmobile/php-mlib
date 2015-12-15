#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

use Oasis\Mlib\Event\Event;

require_once "bootstrap.php";
ini_set('xdebug.var_display_max_depth', 5);

$sqs = new \Oasis\Mlib\AwsWrappers\SqsQueue(
    [
        "profile" => "egg-user",
        "region"  => "us-east-1",
    ],
    'egg-alq'
);

$sqs->addEventListener(
    \Oasis\Mlib\AwsWrappers\SqsQueue::SEND_PROGRESS,
    function (Event $event) {
        minfo(sprintf("Send progress: %.2f%%", $event->getContext() * 100));
    }
);
$sqs->addEventListener(
    \Oasis\Mlib\AwsWrappers\SqsQueue::READ_PROGRESS,
    function (Event $event) {
        minfo(sprintf("Read progress: %.2f%%", $event->getContext() * 100));
    }
);
$sqs->addEventListener(
    \Oasis\Mlib\AwsWrappers\SqsQueue::DELETE_PROGRESS,
    function (Event $event) {
        minfo(sprintf("Delete progress: %.2f%%", $event->getContext() * 100));
    }
);

//mdebug("Sending...");
//$sqs->sendMessages(
//    [
//        "eyJ1aWQiOiJ7MjYwZDZlMzE5Yjc0NDJjYThmZjU5ZjY1NmI2NjJlNDh9IiwiaXAiOiIxNzcuMTM4LjE3OS4xMjEiLCJlZ2ciOjcsInZlcnNpb24iOiIxMDAwLjAuMC4xNDAiLCJjaGFubmVsIjoiMTAyYnJcIiIsIm9zIjoiNi4xIiwibG9jYWxlIjoxMDQ2LCJhbWQ2NCI6ZmFsc2UsImV2ZW50IjoiY2hlY2t1cGRhdGUiLCJkYXRhIjoie1wiYW50aXZpcnVzXCI6W3tcImd1aWRcIjpcInsxN0FEN0Q0MC1CQTEyLTlDNDYtNzEzMS05NDkwM0E1NEFEOEJ9XCIsXCJuYW1lXCI6XCJhdmFzdCEgQW50aXZpcnVzXCJ9XSxcImJyb3dzZXJcIjpcImNocm9tZVwiLFwiYnZlcnNpb25cIjpcIjQ3LjAuMjUyNi44MFwiLFwiZG90bmV0XCI6W1wiMi4wLjUwNzI3LjQ5MjdcIixcIjMuMC4zMDcyOS40OTI2XCIsXCIzLjUuMzA3MjkuNDkyNlwiLFwiNC4wLjMwMzE5XCJdLFwiZHhcIjpcIjExXCIsXCJlZ2dpZFwiOjcsXCJoYXJkd2FyZVwiOm51bGwsXCJpZVwiOlwiOS4wLjgxMTIuMTY0MjFcIixcImtpbGxcIjpcIlwiLFwicmVxdWlyZVwiOlwiXCIsXCJzb2Z0d2FyZVwiOntcImNocm9tZVwiOm51bGwsXCJndXBkYXRlXCI6bnVsbH19IiwidGltZXN0YW1wIjoxNDUwMTA2OTQxLCJldmVudF9kYXRlIjoiMjAxNTEyMTQifQ==",
//        "eyJ1aWQiOiJ7ZTdkNGU4NTMyZmM1NGQ1N2JmYWI4ZjdlYjgzNGMzMGJ9IiwiaXAiOiIxOTAuMTk2LjE3OC4yMzgiLCJlZ2ciOjksInZlcnNpb24iOiIyLjEuMC4zMiIsImNoYW5uZWwiOiJlZ2c5Iiwib3MiOiI2LjMuOTYwMC4xNzAzMSIsImxvY2FsZSI6MzA4MiwiYW1kNjQiOnRydWUsImV2ZW50IjoiY2hlY2t1cGRhdGUyIiwiZGF0YSI6Im51bGwiLCJ0aW1lc3RhbXAiOjE0NTAxMDY5NDIsImV2ZW50X2RhdGUiOiIyMDE1MTIxNCJ9",
//        "eyJ1aWQiOiJ7MjYwZDZlMzE5Yjc0NDJjYThmZjU5ZjY1NmI2NjJlNDh9IiwiaXAiOiIxNzcuMTM4LjE3OS4xMjEiLCJlZ2ciOjcsInZlcnNpb24iOiIxMDAwLjAuMC4xNDAiLCJjaGFubmVsIjoiMTAyYnJcIiIsIm9zIjoiNi4xIiwibG9jYWxlIjoxMDQ2LCJhbWQ2NCI6ZmFsc2UsImV2ZW50IjoiY2hlY2t1cGRhdGUiLCJkYXRhIjoie1wiYW50aXZpcnVzXCI6W3tcImd1aWRcIjpcInsxN0FEN0Q0MC1CQTEyLTlDNDYtNzEzMS05NDkwM0E1NEFEOEJ9XCIsXCJuYW1lXCI6XCJhdmFzdCEgQW50aXZpcnVzXCJ9XSxcImJyb3dzZXJcIjpcImNocm9tZVwiLFwiYnZlcnNpb25cIjpcIjQ3LjAuMjUyNi44MFwiLFwiZG90bmV0XCI6W1wiMi4wLjUwNzI3LjQ5MjdcIixcIjMuMC4zMDcyOS40OTI2XCIsXCIzLjUuMzA3MjkuNDkyNlwiLFwiNC4wLjMwMzE5XCJdLFwiZHhcIjpcIjExXCIsXCJlZ2dpZFwiOjcsXCJoYXJkd2FyZVwiOm51bGwsXCJpZVwiOlwiOS4wLjgxMTIuMTY0MjFcIixcImtpbGxcIjpcIlwiLFwicmVxdWlyZVwiOlwiXCIsXCJzb2Z0d2FyZVwiOntcImNocm9tZVwiOm51bGwsXCJndXBkYXRlXCI6bnVsbH19IiwidGltZXN0YW1wIjoxNDUwMTA2OTQxLCJldmVudF9kYXRlIjoiMjAxNTEyMTQifQ==",
//        "eyJ1aWQiOiJ7ZTdkNGU4NTMyZmM1NGQ1N2JmYWI4ZjdlYjgzNGMzMGJ9IiwiaXAiOiIxOTAuMTk2LjE3OC4yMzgiLCJlZ2ciOjksInZlcnNpb24iOiIyLjEuMC4zMiIsImNoYW5uZWwiOiJlZ2c5Iiwib3MiOiI2LjMuOTYwMC4xNzAzMSIsImxvY2FsZSI6MzA4MiwiYW1kNjQiOnRydWUsImV2ZW50IjoiY2hlY2t1cGRhdGUyIiwiZGF0YSI6Im51bGwiLCJ0aW1lc3RhbXAiOjE0NTAxMDY5NDIsImV2ZW50X2RhdGUiOiIyMDE1MTIxNCJ9",
//        "eyJ1aWQiOiJ7MjYwZDZlMzE5Yjc0NDJjYThmZjU5ZjY1NmI2NjJlNDh9IiwiaXAiOiIxNzcuMTM4LjE3OS4xMjEiLCJlZ2ciOjcsInZlcnNpb24iOiIxMDAwLjAuMC4xNDAiLCJjaGFubmVsIjoiMTAyYnJcIiIsIm9zIjoiNi4xIiwibG9jYWxlIjoxMDQ2LCJhbWQ2NCI6ZmFsc2UsImV2ZW50IjoiY2hlY2t1cGRhdGUiLCJkYXRhIjoie1wiYW50aXZpcnVzXCI6W3tcImd1aWRcIjpcInsxN0FEN0Q0MC1CQTEyLTlDNDYtNzEzMS05NDkwM0E1NEFEOEJ9XCIsXCJuYW1lXCI6XCJhdmFzdCEgQW50aXZpcnVzXCJ9XSxcImJyb3dzZXJcIjpcImNocm9tZVwiLFwiYnZlcnNpb25cIjpcIjQ3LjAuMjUyNi44MFwiLFwiZG90bmV0XCI6W1wiMi4wLjUwNzI3LjQ5MjdcIixcIjMuMC4zMDcyOS40OTI2XCIsXCIzLjUuMzA3MjkuNDkyNlwiLFwiNC4wLjMwMzE5XCJdLFwiZHhcIjpcIjExXCIsXCJlZ2dpZFwiOjcsXCJoYXJkd2FyZVwiOm51bGwsXCJpZVwiOlwiOS4wLjgxMTIuMTY0MjFcIixcImtpbGxcIjpcIlwiLFwicmVxdWlyZVwiOlwiXCIsXCJzb2Z0d2FyZVwiOntcImNocm9tZVwiOm51bGwsXCJndXBkYXRlXCI6bnVsbH19IiwidGltZXN0YW1wIjoxNDUwMTA2OTQxLCJldmVudF9kYXRlIjoiMjAxNTEyMTQifQ==",
//        "eyJ1aWQiOiJ7ZTdkNGU4NTMyZmM1NGQ1N2JmYWI4ZjdlYjgzNGMzMGJ9IiwiaXAiOiIxOTAuMTk2LjE3OC4yMzgiLCJlZ2ciOjksInZlcnNpb24iOiIyLjEuMC4zMiIsImNoYW5uZWwiOiJlZ2c5Iiwib3MiOiI2LjMuOTYwMC4xNzAzMSIsImxvY2FsZSI6MzA4MiwiYW1kNjQiOnRydWUsImV2ZW50IjoiY2hlY2t1cGRhdGUyIiwiZGF0YSI6Im51bGwiLCJ0aW1lc3RhbXAiOjE0NTAxMDY5NDIsImV2ZW50X2RhdGUiOiIyMDE1MTIxNCJ9",
//        "eyJ1aWQiOiJ7MjYwZDZlMzE5Yjc0NDJjYThmZjU5ZjY1NmI2NjJlNDh9IiwiaXAiOiIxNzcuMTM4LjE3OS4xMjEiLCJlZ2ciOjcsInZlcnNpb24iOiIxMDAwLjAuMC4xNDAiLCJjaGFubmVsIjoiMTAyYnJcIiIsIm9zIjoiNi4xIiwibG9jYWxlIjoxMDQ2LCJhbWQ2NCI6ZmFsc2UsImV2ZW50IjoiY2hlY2t1cGRhdGUiLCJkYXRhIjoie1wiYW50aXZpcnVzXCI6W3tcImd1aWRcIjpcInsxN0FEN0Q0MC1CQTEyLTlDNDYtNzEzMS05NDkwM0E1NEFEOEJ9XCIsXCJuYW1lXCI6XCJhdmFzdCEgQW50aXZpcnVzXCJ9XSxcImJyb3dzZXJcIjpcImNocm9tZVwiLFwiYnZlcnNpb25cIjpcIjQ3LjAuMjUyNi44MFwiLFwiZG90bmV0XCI6W1wiMi4wLjUwNzI3LjQ5MjdcIixcIjMuMC4zMDcyOS40OTI2XCIsXCIzLjUuMzA3MjkuNDkyNlwiLFwiNC4wLjMwMzE5XCJdLFwiZHhcIjpcIjExXCIsXCJlZ2dpZFwiOjcsXCJoYXJkd2FyZVwiOm51bGwsXCJpZVwiOlwiOS4wLjgxMTIuMTY0MjFcIixcImtpbGxcIjpcIlwiLFwicmVxdWlyZVwiOlwiXCIsXCJzb2Z0d2FyZVwiOntcImNocm9tZVwiOm51bGwsXCJndXBkYXRlXCI6bnVsbH19IiwidGltZXN0YW1wIjoxNDUwMTA2OTQxLCJldmVudF9kYXRlIjoiMjAxNTEyMTQifQ==",
//        "eyJ1aWQiOiJ7ZTdkNGU4NTMyZmM1NGQ1N2JmYWI4ZjdlYjgzNGMzMGJ9IiwiaXAiOiIxOTAuMTk2LjE3OC4yMzgiLCJlZ2ciOjksInZlcnNpb24iOiIyLjEuMC4zMiIsImNoYW5uZWwiOiJlZ2c5Iiwib3MiOiI2LjMuOTYwMC4xNzAzMSIsImxvY2FsZSI6MzA4MiwiYW1kNjQiOnRydWUsImV2ZW50IjoiY2hlY2t1cGRhdGUyIiwiZGF0YSI6Im51bGwiLCJ0aW1lc3RhbXAiOjE0NTAxMDY5NDIsImV2ZW50X2RhdGUiOiIyMDE1MTIxNCJ9",
//        "eyJ1aWQiOiJ7MjYwZDZlMzE5Yjc0NDJjYThmZjU5ZjY1NmI2NjJlNDh9IiwiaXAiOiIxNzcuMTM4LjE3OS4xMjEiLCJlZ2ciOjcsInZlcnNpb24iOiIxMDAwLjAuMC4xNDAiLCJjaGFubmVsIjoiMTAyYnJcIiIsIm9zIjoiNi4xIiwibG9jYWxlIjoxMDQ2LCJhbWQ2NCI6ZmFsc2UsImV2ZW50IjoiY2hlY2t1cGRhdGUiLCJkYXRhIjoie1wiYW50aXZpcnVzXCI6W3tcImd1aWRcIjpcInsxN0FEN0Q0MC1CQTEyLTlDNDYtNzEzMS05NDkwM0E1NEFEOEJ9XCIsXCJuYW1lXCI6XCJhdmFzdCEgQW50aXZpcnVzXCJ9XSxcImJyb3dzZXJcIjpcImNocm9tZVwiLFwiYnZlcnNpb25cIjpcIjQ3LjAuMjUyNi44MFwiLFwiZG90bmV0XCI6W1wiMi4wLjUwNzI3LjQ5MjdcIixcIjMuMC4zMDcyOS40OTI2XCIsXCIzLjUuMzA3MjkuNDkyNlwiLFwiNC4wLjMwMzE5XCJdLFwiZHhcIjpcIjExXCIsXCJlZ2dpZFwiOjcsXCJoYXJkd2FyZVwiOm51bGwsXCJpZVwiOlwiOS4wLjgxMTIuMTY0MjFcIixcImtpbGxcIjpcIlwiLFwicmVxdWlyZVwiOlwiXCIsXCJzb2Z0d2FyZVwiOntcImNocm9tZVwiOm51bGwsXCJndXBkYXRlXCI6bnVsbH19IiwidGltZXN0YW1wIjoxNDUwMTA2OTQxLCJldmVudF9kYXRlIjoiMjAxNTEyMTQifQ==",
//        "eyJ1aWQiOiJ7ZTdkNGU4NTMyZmM1NGQ1N2JmYWI4ZjdlYjgzNGMzMGJ9IiwiaXAiOiIxOTAuMTk2LjE3OC4yMzgiLCJlZ2ciOjksInZlcnNpb24iOiIyLjEuMC4zMiIsImNoYW5uZWwiOiJlZ2c5Iiwib3MiOiI2LjMuOTYwMC4xNzAzMSIsImxvY2FsZSI6MzA4MiwiYW1kNjQiOnRydWUsImV2ZW50IjoiY2hlY2t1cGRhdGUyIiwiZGF0YSI6Im51bGwiLCJ0aW1lc3RhbXAiOjE0NTAxMDY5NDIsImV2ZW50X2RhdGUiOiIyMDE1MTIxNCJ9",
//        "eyJ1aWQiOiJ7MjYwZDZlMzE5Yjc0NDJjYThmZjU5ZjY1NmI2NjJlNDh9IiwiaXAiOiIxNzcuMTM4LjE3OS4xMjEiLCJlZ2ciOjcsInZlcnNpb24iOiIxMDAwLjAuMC4xNDAiLCJjaGFubmVsIjoiMTAyYnJcIiIsIm9zIjoiNi4xIiwibG9jYWxlIjoxMDQ2LCJhbWQ2NCI6ZmFsc2UsImV2ZW50IjoiY2hlY2t1cGRhdGUiLCJkYXRhIjoie1wiYW50aXZpcnVzXCI6W3tcImd1aWRcIjpcInsxN0FEN0Q0MC1CQTEyLTlDNDYtNzEzMS05NDkwM0E1NEFEOEJ9XCIsXCJuYW1lXCI6XCJhdmFzdCEgQW50aXZpcnVzXCJ9XSxcImJyb3dzZXJcIjpcImNocm9tZVwiLFwiYnZlcnNpb25cIjpcIjQ3LjAuMjUyNi44MFwiLFwiZG90bmV0XCI6W1wiMi4wLjUwNzI3LjQ5MjdcIixcIjMuMC4zMDcyOS40OTI2XCIsXCIzLjUuMzA3MjkuNDkyNlwiLFwiNC4wLjMwMzE5XCJdLFwiZHhcIjpcIjExXCIsXCJlZ2dpZFwiOjcsXCJoYXJkd2FyZVwiOm51bGwsXCJpZVwiOlwiOS4wLjgxMTIuMTY0MjFcIixcImtpbGxcIjpcIlwiLFwicmVxdWlyZVwiOlwiXCIsXCJzb2Z0d2FyZVwiOntcImNocm9tZVwiOm51bGwsXCJndXBkYXRlXCI6bnVsbH19IiwidGltZXN0YW1wIjoxNDUwMTA2OTQxLCJldmVudF9kYXRlIjoiMjAxNTEyMTQifQ==",
//        "eyJ1aWQiOiJ7ZTdkNGU4NTMyZmM1NGQ1N2JmYWI4ZjdlYjgzNGMzMGJ9IiwiaXAiOiIxOTAuMTk2LjE3OC4yMzgiLCJlZ2ciOjksInZlcnNpb24iOiIyLjEuMC4zMiIsImNoYW5uZWwiOiJlZ2c5Iiwib3MiOiI2LjMuOTYwMC4xNzAzMSIsImxvY2FsZSI6MzA4MiwiYW1kNjQiOnRydWUsImV2ZW50IjoiY2hlY2t1cGRhdGUyIiwiZGF0YSI6Im51bGwiLCJ0aW1lc3RhbXAiOjE0NTAxMDY5NDIsImV2ZW50X2RhdGUiOiIyMDE1MTIxNCJ9",
//        "eyJ1aWQiOiJ7MjYwZDZlMzE5Yjc0NDJjYThmZjU5ZjY1NmI2NjJlNDh9IiwiaXAiOiIxNzcuMTM4LjE3OS4xMjEiLCJlZ2ciOjcsInZlcnNpb24iOiIxMDAwLjAuMC4xNDAiLCJjaGFubmVsIjoiMTAyYnJcIiIsIm9zIjoiNi4xIiwibG9jYWxlIjoxMDQ2LCJhbWQ2NCI6ZmFsc2UsImV2ZW50IjoiY2hlY2t1cGRhdGUiLCJkYXRhIjoie1wiYW50aXZpcnVzXCI6W3tcImd1aWRcIjpcInsxN0FEN0Q0MC1CQTEyLTlDNDYtNzEzMS05NDkwM0E1NEFEOEJ9XCIsXCJuYW1lXCI6XCJhdmFzdCEgQW50aXZpcnVzXCJ9XSxcImJyb3dzZXJcIjpcImNocm9tZVwiLFwiYnZlcnNpb25cIjpcIjQ3LjAuMjUyNi44MFwiLFwiZG90bmV0XCI6W1wiMi4wLjUwNzI3LjQ5MjdcIixcIjMuMC4zMDcyOS40OTI2XCIsXCIzLjUuMzA3MjkuNDkyNlwiLFwiNC4wLjMwMzE5XCJdLFwiZHhcIjpcIjExXCIsXCJlZ2dpZFwiOjcsXCJoYXJkd2FyZVwiOm51bGwsXCJpZVwiOlwiOS4wLjgxMTIuMTY0MjFcIixcImtpbGxcIjpcIlwiLFwicmVxdWlyZVwiOlwiXCIsXCJzb2Z0d2FyZVwiOntcImNocm9tZVwiOm51bGwsXCJndXBkYXRlXCI6bnVsbH19IiwidGltZXN0YW1wIjoxNDUwMTA2OTQxLCJldmVudF9kYXRlIjoiMjAxNTEyMTQifQ==",
//        "eyJ1aWQiOiJ7ZTdkNGU4NTMyZmM1NGQ1N2JmYWI4ZjdlYjgzNGMzMGJ9IiwiaXAiOiIxOTAuMTk2LjE3OC4yMzgiLCJlZ2ciOjksInZlcnNpb24iOiIyLjEuMC4zMiIsImNoYW5uZWwiOiJlZ2c5Iiwib3MiOiI2LjMuOTYwMC4xNzAzMSIsImxvY2FsZSI6MzA4MiwiYW1kNjQiOnRydWUsImV2ZW50IjoiY2hlY2t1cGRhdGUyIiwiZGF0YSI6Im51bGwiLCJ0aW1lc3RhbXAiOjE0NTAxMDY5NDIsImV2ZW50X2RhdGUiOiIyMDE1MTIxNCJ9",
//        "eyJ1aWQiOiJ7MjYwZDZlMzE5Yjc0NDJjYThmZjU5ZjY1NmI2NjJlNDh9IiwiaXAiOiIxNzcuMTM4LjE3OS4xMjEiLCJlZ2ciOjcsInZlcnNpb24iOiIxMDAwLjAuMC4xNDAiLCJjaGFubmVsIjoiMTAyYnJcIiIsIm9zIjoiNi4xIiwibG9jYWxlIjoxMDQ2LCJhbWQ2NCI6ZmFsc2UsImV2ZW50IjoiY2hlY2t1cGRhdGUiLCJkYXRhIjoie1wiYW50aXZpcnVzXCI6W3tcImd1aWRcIjpcInsxN0FEN0Q0MC1CQTEyLTlDNDYtNzEzMS05NDkwM0E1NEFEOEJ9XCIsXCJuYW1lXCI6XCJhdmFzdCEgQW50aXZpcnVzXCJ9XSxcImJyb3dzZXJcIjpcImNocm9tZVwiLFwiYnZlcnNpb25cIjpcIjQ3LjAuMjUyNi44MFwiLFwiZG90bmV0XCI6W1wiMi4wLjUwNzI3LjQ5MjdcIixcIjMuMC4zMDcyOS40OTI2XCIsXCIzLjUuMzA3MjkuNDkyNlwiLFwiNC4wLjMwMzE5XCJdLFwiZHhcIjpcIjExXCIsXCJlZ2dpZFwiOjcsXCJoYXJkd2FyZVwiOm51bGwsXCJpZVwiOlwiOS4wLjgxMTIuMTY0MjFcIixcImtpbGxcIjpcIlwiLFwicmVxdWlyZVwiOlwiXCIsXCJzb2Z0d2FyZVwiOntcImNocm9tZVwiOm51bGwsXCJndXBkYXRlXCI6bnVsbH19IiwidGltZXN0YW1wIjoxNDUwMTA2OTQxLCJldmVudF9kYXRlIjoiMjAxNTEyMTQifQ==",
//        "eyJ1aWQiOiJ7ZTdkNGU4NTMyZmM1NGQ1N2JmYWI4ZjdlYjgzNGMzMGJ9IiwiaXAiOiIxOTAuMTk2LjE3OC4yMzgiLCJlZ2ciOjksInZlcnNpb24iOiIyLjEuMC4zMiIsImNoYW5uZWwiOiJlZ2c5Iiwib3MiOiI2LjMuOTYwMC4xNzAzMSIsImxvY2FsZSI6MzA4MiwiYW1kNjQiOnRydWUsImV2ZW50IjoiY2hlY2t1cGRhdGUyIiwiZGF0YSI6Im51bGwiLCJ0aW1lc3RhbXAiOjE0NTAxMDY5NDIsImV2ZW50X2RhdGUiOiIyMDE1MTIxNCJ9",
//        "eyJ1aWQiOiJ7MjYwZDZlMzE5Yjc0NDJjYThmZjU5ZjY1NmI2NjJlNDh9IiwiaXAiOiIxNzcuMTM4LjE3OS4xMjEiLCJlZ2ciOjcsInZlcnNpb24iOiIxMDAwLjAuMC4xNDAiLCJjaGFubmVsIjoiMTAyYnJcIiIsIm9zIjoiNi4xIiwibG9jYWxlIjoxMDQ2LCJhbWQ2NCI6ZmFsc2UsImV2ZW50IjoiY2hlY2t1cGRhdGUiLCJkYXRhIjoie1wiYW50aXZpcnVzXCI6W3tcImd1aWRcIjpcInsxN0FEN0Q0MC1CQTEyLTlDNDYtNzEzMS05NDkwM0E1NEFEOEJ9XCIsXCJuYW1lXCI6XCJhdmFzdCEgQW50aXZpcnVzXCJ9XSxcImJyb3dzZXJcIjpcImNocm9tZVwiLFwiYnZlcnNpb25cIjpcIjQ3LjAuMjUyNi44MFwiLFwiZG90bmV0XCI6W1wiMi4wLjUwNzI3LjQ5MjdcIixcIjMuMC4zMDcyOS40OTI2XCIsXCIzLjUuMzA3MjkuNDkyNlwiLFwiNC4wLjMwMzE5XCJdLFwiZHhcIjpcIjExXCIsXCJlZ2dpZFwiOjcsXCJoYXJkd2FyZVwiOm51bGwsXCJpZVwiOlwiOS4wLjgxMTIuMTY0MjFcIixcImtpbGxcIjpcIlwiLFwicmVxdWlyZVwiOlwiXCIsXCJzb2Z0d2FyZVwiOntcImNocm9tZVwiOm51bGwsXCJndXBkYXRlXCI6bnVsbH19IiwidGltZXN0YW1wIjoxNDUwMTA2OTQxLCJldmVudF9kYXRlIjoiMjAxNTEyMTQifQ==",
//        "eyJ1aWQiOiJ7ZTdkNGU4NTMyZmM1NGQ1N2JmYWI4ZjdlYjgzNGMzMGJ9IiwiaXAiOiIxOTAuMTk2LjE3OC4yMzgiLCJlZ2ciOjksInZlcnNpb24iOiIyLjEuMC4zMiIsImNoYW5uZWwiOiJlZ2c5Iiwib3MiOiI2LjMuOTYwMC4xNzAzMSIsImxvY2FsZSI6MzA4MiwiYW1kNjQiOnRydWUsImV2ZW50IjoiY2hlY2t1cGRhdGUyIiwiZGF0YSI6Im51bGwiLCJ0aW1lc3RhbXAiOjE0NTAxMDY5NDIsImV2ZW50X2RhdGUiOiIyMDE1MTIxNCJ9",
//    ]
//);
mdebug("Reading...");
$msgs = $sqs->receiveMessages(50, null, 60);
mdebug("Deleting...");
$sqs->deleteMessages($msgs);
mdebug("Done");
