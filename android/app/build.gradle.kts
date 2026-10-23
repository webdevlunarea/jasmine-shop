import java.util.Properties

plugins {
    alias(libs.plugins.android.application)
    alias(libs.plugins.kotlin.android)
    alias(libs.plugins.kotlin.compose)
    alias(libs.plugins.google.services)
}

val uploadSigningPropertiesFile = rootProject.file("keystore.properties")
val uploadSigningProperties = Properties().apply {
    if (uploadSigningPropertiesFile.isFile) {
        uploadSigningPropertiesFile.inputStream().use { load(it) }
    }
}
val uploadSigningKeys = listOf("storeFile", "storePassword", "keyAlias", "keyPassword")
val hasUploadSigning = uploadSigningKeys.all { !uploadSigningProperties.getProperty(it).isNullOrBlank() }
val releaseArtifactRequested = gradle.startParameter.taskNames.any {
    it.substringAfterLast(':').matches(Regex("(?i)(bundle|assemble|package)Release"))
}
if (releaseArtifactRequested && !hasUploadSigning) {
    throw GradleException(
        "Release signing is not configured. Create android/keystore.properties using " +
            "android/keystore.properties.example and your Play upload keystore."
    )
}

android {
    namespace = "com.lunareafurniture.lunarea"
    compileSdk = 36
    defaultConfig {
        applicationId = "com.lunareafurniture.lunarea"
        minSdk = 26
        targetSdk = 36
        versionCode = 5
        versionName = "0.1.3"
        buildConfigField("String", "API_BASE_URL", "\"https://lunareafurniture.com/api/v1/\"")
        buildConfigField("String", "GOOGLE_WEB_CLIENT_ID", "\"520531202813-voubv1urfgrmeg8l813i4d3qh9r8i2g6.apps.googleusercontent.com\"")
        buildConfigField("String", "RECAPTCHA_ANDROID_KEY_ID", "\"6LdhT_YtAAAAAPXNKQu3-4owL1D6Yb97D4qZzIW_\"")
    }
    signingConfigs {
        if (hasUploadSigning) {
            create("playUpload") {
                storeFile = rootProject.file(uploadSigningProperties.getProperty("storeFile"))
                storePassword = uploadSigningProperties.getProperty("storePassword")
                keyAlias = uploadSigningProperties.getProperty("keyAlias")
                keyPassword = uploadSigningProperties.getProperty("keyPassword")
            }
        }
    }
    buildTypes {
        getByName("release") {
            if (hasUploadSigning) signingConfig = signingConfigs.getByName("playUpload")
            ndk {
                debugSymbolLevel = "FULL"
            }
        }
    }
    buildFeatures { compose = true; buildConfig = true }
    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
}
kotlin {
    compilerOptions {
        jvmTarget.set(org.jetbrains.kotlin.gradle.dsl.JvmTarget.JVM_17)
    }
}
dependencies {
    implementation(libs.androidx.core.ktx)
    implementation(libs.androidx.core.splashscreen)
    implementation(libs.androidx.activity.compose)
    implementation(libs.androidx.biometric)
    implementation(libs.androidx.lifecycle.runtime.ktx)
    implementation(libs.androidx.lifecycle.viewmodel.compose)
    implementation(platform(libs.androidx.compose.bom))
    implementation(platform(libs.firebase.bom))
    implementation(libs.firebase.messaging)
    implementation(libs.androidx.credentials)
    implementation(libs.androidx.credentials.play.services.auth)
    implementation(libs.googleid)
    implementation(libs.coil.compose)
    implementation(libs.google.recaptcha)
    implementation(libs.zxing.core)
    implementation(libs.androidx.compose.ui)
    implementation(libs.androidx.compose.ui.graphics)
    implementation(libs.androidx.compose.ui.tooling.preview)
    implementation(libs.androidx.compose.material3)
    implementation(libs.retrofit)
    implementation(libs.retrofit.converter.gson)
    implementation(libs.okhttp)
    implementation(libs.okhttp.logging)
    implementation(libs.coroutines.android)
    implementation(libs.coroutines.play.services)
    implementation(libs.play.services.location)
    implementation(libs.datastore.preferences)
    implementation(libs.work.runtime.ktx)
    debugImplementation(libs.androidx.compose.ui.tooling)
}
