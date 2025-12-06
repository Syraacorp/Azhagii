package com.pin2fix;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.context.event.ApplicationReadyEvent;
import org.springframework.context.event.EventListener;
import org.springframework.beans.factory.annotation.Value;

@SpringBootApplication
public class Pin2FixApplication {

    @Value("${server.port:8080}")
    private String serverPort;

    public static void main(String[] args) {
        SpringApplication.run(Pin2FixApplication.class, args);
    }

    @EventListener(ApplicationReadyEvent.class)
    public void onApplicationReady() {
        System.out.println("\n" + "=".repeat(60));
        System.out.println("  ____  _       ____  _____ _      ");
        System.out.println(" |  _ \\(_)_ __ |___ \\|  ___(_)_  __");
        System.out.println(" | |_) | | '_ \\  __) | |_  | \\ \\/ /");
        System.out.println(" |  __/| | | | |/ __/|  _| | |>  < ");
        System.out.println(" |_|   |_|_| |_|_____|_|   |_/_/\\_\\");
        System.out.println("\n" + "=".repeat(60));
        System.out.println("  Pin2Fix Application Started Successfully!");
        System.out.println("=".repeat(60));
        System.out.println("\n  Open in browser:");
        System.out.println("  ➤ Local:   http://localhost:" + serverPort);
        System.out.println("  ➤ Home:    http://localhost:" + serverPort + "/index.html");
        System.out.println("  ➤ Login:   http://localhost:" + serverPort + "/login.html");
        System.out.println("\n" + "=".repeat(60) + "\n");
    }
}
